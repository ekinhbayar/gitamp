const GitAmp = (function(exports, $) {
    'use strict';

    /**
     * AudioPlayer
     */
    const AudioPlayer = (function() {
        // something somewhere needs a global volume variable
        // not sure what thing it is, but adding this line works
        exports.volume = localStorage.getItem('volume');

        const maxPitch = 100.0;
        const logUsed  = 1.0715307808111486871978099;

        const maximumSimultaneousNotes = 2;
        const soundLength = 300;

        function AudioPlayer() {
            this.currentlyPlayingSounds = 0;

            this.sounds = {
                celesta: this.initializeCelesta(),
                clav: this.initializeClav(),
                swells: this.initializeSwells(),
                easterEggs: this.initializeEasterEggs()
            };

            //noinspection JSUnresolvedVariable
            exports.Howler.volume(localStorage.getItem('volume'));
        }

        AudioPlayer.prototype.initializeCelesta = function() {
            const sounds = [];

            for (let i = 1; i <= 24; i++) {
                let filename = (i > 9) ? 'c0' + i : 'c00' + i;

                //noinspection JSUnresolvedFunction
                sounds.push(new Howl({
                    src : [
                        'https://d1fz9d31zqor6x.cloudfront.net/sounds/celesta/' + filename + '.ogg',
                        'https://d1fz9d31zqor6x.cloudfront.net/sounds/celesta/' + filename + '.mp3'
                    ],
                    volume : 0.7,
                    buffer: true
                }));
            }

            return sounds;
        };

        AudioPlayer.prototype.initializeClav = function() {
            const sounds = [];

            for (let i = 1; i <= 24; i++) {
                let filename = (i > 9) ? 'c0' + i : 'c00' + i;

                //noinspection JSUnresolvedFunction
                sounds.push(new Howl({
                    src : [
                        'https://d1fz9d31zqor6x.cloudfront.net/sounds/clav/' + filename + '.ogg',
                        'https://d1fz9d31zqor6x.cloudfront.net/sounds/clav/' + filename + '.mp3'
                    ],
                    volume : 0.7,
                    buffer: true
                }));
            }

            return sounds;
        };

        AudioPlayer.prototype.initializeSwells = function() {
            const sounds = [];

            for (let i = 1; i <= 3; i++) {
                //noinspection JSUnresolvedFunction
                sounds.push(new Howl({
                    src : [
                        'https://d1fz9d31zqor6x.cloudfront.net/sounds/swells/swell' + i + '.ogg',
                        'https://d1fz9d31zqor6x.cloudfront.net/sounds/swells/swell' + i + '.mp3'
                    ],
                    volume : 0.7,
                    buffer: true
                }));
            }

            return sounds;
        };

        AudioPlayer.prototype.initializeEasterEggs = function() {
            const sounds = {};

            const eggs = ['celesta', 'clav', 'swell'];

            for (let i = 0; i < eggs.length; i++) {
                //noinspection JSUnresolvedFunction
                sounds[eggs[i]] = new Howl({
                    src : [
                        '/sounds/egg/' + eggs[i] + '.ogg',
                        '/sounds/egg/' + eggs[i] + '.mp3'
                    ],
                    volume : 0.7,
                    buffer: true
                });
            }

            return sounds;
        };

        AudioPlayer.prototype.getSoundIndex = function(size, type) {
            const pitch = 100 - Math.min(maxPitch, Math.log(size + logUsed) / Math.log(logUsed));
            let index   = Math.floor(pitch / 100.0 * this.sounds[type].length);

            index += Math.floor(Math.random() * 4) - 2;
            index = Math.min(this.sounds[type].length - 1, index);
            index = Math.max(1, index);

            return index;
        };

        AudioPlayer.prototype.playSound = function(sound) {
            if (this.currentlyPlayingSounds >= maximumSimultaneousNotes) {
                return;
            }

            sound.play();

            this.currentlyPlayingSounds++;

            setTimeout(function() {
                this.currentlyPlayingSounds--;
            }.bind(this), soundLength);
        };

        AudioPlayer.prototype.playCelesta = function(size) {
            this.playSound(this.sounds.celesta[this.getSoundIndex(size, 'celesta')]);
        };

        AudioPlayer.prototype.playClav = function(size) {
            this.playSound(this.sounds.clav[this.getSoundIndex(size, 'clav')]);
        };

        AudioPlayer.prototype.playSwell = function() {
            this.playSound(this.sounds.swells[Math.round(Math.random() * (this.sounds.swells.length - 1))]);
        };

        AudioPlayer.prototype.playCelestaEgg = function() {
            //noinspection JSUnresolvedVariable
            this.playSound(this.sounds.easterEggs.celesta);
        };

        AudioPlayer.prototype.playClavEgg = function() {
            //noinspection JSUnresolvedVariable
            this.playSound(this.sounds.easterEggs.clav);
        };

        AudioPlayer.prototype.playSwellEgg = function() {
            //noinspection JSUnresolvedVariable
            this.playSound(this.sounds.easterEggs.swell);
        };

        return AudioPlayer;
    }());

    /**
     * Gui
     */
    const Gui = (function() {
        const scaleFactor = 6;
        const textColor   = '#ffffff';
        const maxLife     = 20000;

        function Event(event, svg) {
            this.event = event;
            this.svg   = svg;
        }

        Event.prototype.getSize = function() {
            return Math.max(Math.sqrt(Math.abs(this.event.getPayload().length)) * scaleFactor, 3);
        };

        Event.prototype.getText = function() {
            return this.event.getMessage();
        };

        Event.prototype.getClassName = function() {
            return 'event-' + this.event.getType();
        };

        Event.prototype.getRingAnimationDuration = function() {
            return this.event.getRingAnimationDuration();
        };

        Event.prototype.getRingRadius = function() {
            return this.event.getRingRadius();
        };

        Event.prototype.draw = function(width, height) {
            let no_label = false;
            let size     = this.getSize();

            const self = this;

            //noinspection JSUnresolvedFunction
            Math.seedrandom(this.event.getPayload());
            let x = Math.random() * (width - size) + size;
            let y = Math.random() * (height - size) + size;

            let circle_group = this.svg.append('g')
                .classed(this.getClassName(), true)
                .attr('transform', 'translate(' + x + ', ' + y + ')')
                .style('opacity', 1);

            let ring = circle_group.append('circle');
            ring.attr({r: size, stroke: 'none'});
            ring.transition()
                .attr('r', size + this.getRingRadius())
                .style('opacity', 0)
                .ease(Math.sqrt)
                .duration(this.getRingAnimationDuration())
                .remove();

            let circle_container = circle_group.append('a');
            circle_container.attr('xlink:href', this.event.getUrl());
            circle_container.attr('target', '_blank');
            circle_container.attr('fill', textColor);

            let circle = circle_container.append('circle');
            circle.classed(this.getClassName(), true);
            circle.attr('r', size)
                .transition()
                .duration(maxLife)
                .style('opacity', 0)
                .remove();

            circle_container.on('mouseover', function() {
                circle_container.append('text')
                    .text(self.getText())
                    .classed('label', true)
                    .attr('text-anchor', 'middle')
                    .attr('font-size', '0.8em')
                    .transition()
                    .delay(1000)
                    .style('opacity', 0)
                    .duration(2000)
                    .each(function() { no_label = true; })
                    .remove();
            });

            circle_container.append('text')
                .text(this.getText())
                .classed('article-label', true)
                .attr('text-anchor', 'middle')
                .attr('font-size', '0.8em')
                .transition()
                .delay(2000)
                .style('opacity', 0)
                .duration(5000)
                .each(function() { no_label = true; })
                .remove();
        };

        function Gui() {
            //noinspection JSUnresolvedVariable
            this.svg = exports.d3.select('#area').append('svg');

            exports.addEventListener('resize', this.resize.bind(this));

            this.setupVolumeSlider();
            this.resize();
        }

        Gui.prototype.setupVolumeSlider = function() {
            //noinspection JSUnresolvedFunction
            $('#volumeSlider').slider({
                max: 100,
                min: 0,
                value: localStorage.getItem('volume') * 100,
                slide: function (event, ui) {
                    //noinspection JSUnresolvedVariable
                    exports.Howler.volume(ui.value/100.0);

                    localStorage.setItem('volume', ui.value/100.0);
                },
                change: function (event, ui) {
                    //noinspection JSUnresolvedVariable
                    exports.Howler.volume(ui.value/100.0);

                    localStorage.setItem('volume', ui.value/100.0);
                }
            });
        };

        Gui.prototype.getWidth = function() {
            return exports.innerWidth;
        };

        Gui.prototype.getHeight = function() {
            return exports.innerHeight;
        };

        Gui.prototype.resize = function() {
            this.svg.attr('width', this.getWidth());
            this.svg.attr('height', this.getHeight());
        };

        Gui.prototype.drawEvent = function(event) {
            if (document.hidden) {
                return;
            }

            new Event(event, this.svg).draw(this.getWidth(), this.getHeight());

            // Remove HTML of decayed events
            // Keep it less than 50
            let $area = $('#area');
            if($area.find('svg g').length > 50){
                $area.find('svg g:lt(10)').remove();
            }
        };

        return Gui;
    }());

    /**
     * ConnectedUsersMessage
     */
    function ConnectedUsersMessage(response) {
        //noinspection JSUnresolvedVariable
        this.count = response.connectedUsers;
    }

    ConnectedUsersMessage.prototype.getCount = function() {
        return this.count;
    };

    /**
     * EventMessage
     */
    function EventMessage(event) {
        this.event = event;
    }

    EventMessage.prototype.getId = function() {
        //noinspection JSUnresolvedVariable
        return this.event.id;
    };

    EventMessage.prototype.getType = function() {
        //noinspection JSUnresolvedVariable
        return this.event.type;
    };

    EventMessage.prototype.getUrl = function() {
        //noinspection JSUnresolvedVariable
        return this.event.url;
    };

    EventMessage.prototype.getPayload = function() {
        //noinspection JSUnresolvedVariable
        return this.event.payload;
    };

    EventMessage.prototype.getMessage = function() {
        //noinspection JSUnresolvedVariable
        return this.event.message;
    };

    EventMessage.prototype.getRingAnimationDuration = function() {
        //noinspection JSUnresolvedVariable
        return this.event.ring.animationDuration;
    };

    EventMessage.prototype.getRingRadius = function() {
        //noinspection JSUnresolvedVariable
        return this.event.ring.radius;
    };

    EventMessage.prototype.getSoundType = function() {
        //noinspection JSUnresolvedVariable
        return this.event.sound.type;
    };

    EventMessage.prototype.getSoundSize = function() {
        //noinspection JSUnresolvedVariable
        return this.event.sound.size;
    };

    /**
     * EventMessageCollection
     */
    function EventMessageCollection(response) {
        this.events = [];

        for (let i = 0; i < response.length; i++) {
            this.events.push(new EventMessage(response[i]));
        }
    }

    EventMessageCollection.prototype.forEach = function(callback) {
        for (let i = 0; i < this.events.length; i++) {
            callback(this.events[i]);
        }
    };

    /**
     * EventMessagesFactory
     */
    function EventMessagesFactory () {
    }

    EventMessagesFactory.prototype.build = function(response) {
        const parsedResponse = JSON.parse(response.data);

        if (parsedResponse.hasOwnProperty('connectedUsers')) {
            return new ConnectedUsersMessage(parsedResponse);
        }

        return new EventMessageCollection(parsedResponse);
    };

    /**
     * EventQueue
     */
    function EventQueue() {
        this.queue = [];
    }

    EventQueue.prototype.append = function(eventMessages) {
        eventMessages.forEach(function(event) {
            if (this.exists(event)) {
                return;
            }

            this.queue.push(event);
        }.bind(this));

        if (this.queue.length > 1000) {
            this.queue = this.queue.slice(0, 1000);
        }
    };

    EventQueue.prototype.exists = function(event) {
        for (let i = 0; i < this.queue.length; i++) {
            if (event.getId() === this.queue[i].getId()) {
                return true;
            }
        }

        return false;
    };

    EventQueue.prototype.get = function() {
        return this.queue.shift();
    };

    EventQueue.prototype.count = function() {
        return this.queue.length;
    };

    /**
     * Connection
     */
    function Connection(eventMessageFactory) {
        this.eventMessageFactory = eventMessageFactory;

        this.connection = null;
        this.handlers   = [];
    }

    Connection.prototype.start = function() {
        let protocol = 'ws://';

        if (exports.location.protocol === "https:") {
            protocol = 'wss://';
        }

        try {
            this.connection = new WebSocket(protocol + exports.location.host + '/ws');

            this.connection.addEventListener('message', this.handleMessage.bind(this));
            this.connection.addEventListener('open', this.handleOpen.bind(this));
            this.connection.addEventListener('close', this.reconnect.bind(this));
            this.connection.addEventListener('error', this.reconnect.bind(this));
        } catch(e) {
            this.connection = null;
        }
    };

    Connection.prototype.registerHandler = function(handler) {
        this.handlers.push(handler);
    };

    Connection.prototype.handleMessage = function(response) {
        const message = this.eventMessageFactory.build(response);

        for (let i = 0; i < this.handlers.length; i++) {
            this.handlers[i](message);
        }
    };

    Connection.prototype.handleOpen = function() {
        const elements = document.querySelectorAll('.online-users-div');

        for (let i = 0; i < elements.length; i++) {
            elements[i].style.visibility = 'visible';
        }
    };

    Connection.prototype.reconnect = function() {
        // prevent piling up reconnect
        if (this.connection.readyState === 0 || this.connection.readyState === 1) {
            return;
        }

        setTimeout(function() {
            this.start();
        }.bind(this), 5000);
    };

    /**
     * Application
     */
    function Application() {
        if (localStorage.getItem('volume') === null) {
            localStorage.setItem('volume', 0.6);
        }

        this.queue = new EventQueue();
        this.audio = new AudioPlayer();
        this.gui   = new Gui();
    }

    Application.prototype.run = function() {
        const connection = new Connection(new EventMessagesFactory());

        connection.registerHandler(this.process.bind(this));

        connection.start();

        this.loop();
    };

    Application.prototype.process = function(message) {
        if (message instanceof ConnectedUsersMessage) {
            document.getElementsByClassName('online-users-count')[0].textContent = message.getCount();

            return;
        }

        this.queue.append(message);
    };

    Application.prototype.loop = function() {
        setTimeout(function() {
            this.loop();

            if (!this.queue.count()) {
                return;
            }

            this.processEvent(this.queue.get());

            document.getElementsByClassName('events-remaining-value')[0].textContent = this.queue.count();
        }.bind(this), Math.floor(Math.random() * 1000) + 500);
    };

    Application.prototype.processEvent = function(event) {
        if (!event.getPayload()) {
            return;
        }

        this.audio['play' + event.getSoundType()](event.getSoundSize());

        this.gui.drawEvent(event);
    };

    return Application;
}(window, jQuery));

$(function() {
    new GitAmp().run();
});
