const GitAmp = (function(exports, $) {
    'use strict';

    /**
     * AudioPlayer
     */
    const AudioPlayer = (function() {
        // something somewhere needs a global volume variable
        // not sure what thing it is, but adding this line works
        exports.volume = 0.6;

        const maxPitch = 100.0;
        const logUsed  = 1.0715307808111486871978099;

        const maximumSimultaneousNotes = 2;
        const soundLength = 300;

        function AudioPlayer() {
            this.currentlyPlayingSounds = 0;

            this.sounds = {
                celesta: this.initializeCelesta(),
                clav: this.initializeClav(),
                swells: this.initializeSwells()
            };

            //noinspection JSUnresolvedVariable
            exports.Howler.volume(0.7);
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

        return AudioPlayer;
    }());

    /**
     * Gui
     */
    const Gui = (function() {
        const scaleFactor = 6;
        const textColor   = '#ffffff';
        const maxLife     = 20000;

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
                value: volume * 100,
                slide: function (event, ui) {
                    //noinspection JSUnresolvedVariable
                    exports.Howler.volume(ui.value/100.0);
                },
                change: function (event, ui) {
                    //noinspection JSUnresolvedVariable
                    exports.Howler.volume(ui.value/100.0);
                }
            });
        };

        Gui.prototype.getWidth = function() {
            return exports.innerWidth;
        };

        Gui.prototype.getHeight = function() {
            return exports.innerHeight - $('header').height();
        };

        Gui.prototype.resize = function() {
            this.svg.attr('width', this.getWidth());
            this.svg.attr('height', this.getHeight());
        };

        Gui.prototype.drawEvent = function(event) {
            let opacity = 1 / (100 / event.getMessage().length);

            if (opacity > 0.5) {
                opacity = 0.5;
            }

            let size = event.getMessage().length;
            let label_text;
            let ring_radius = 80;
            let ring_anim_duration = 3000;

            let edit_color = '#FFFFFF';

            switch(event.getType()){
                case "PushEvent":
                    label_text = event.getActorName() + " pushed to " + event.getRepositoryName();
                    edit_color = '#22B65D';
                    break;
                case "PullRequestEvent":
                    label_text = event.getActorName() + " " +
                        event.getAction() + " " + " a PR for " + event.getRepositoryName();
                    edit_color = '#8F19BB';
                    ring_anim_duration = 10000;
                    ring_radius = 600;
                    break;
                case "IssuesEvent":
                    label_text = event.getActorName() + " " +
                        event.getAction() + " an issue in " + event.getRepositoryName();
                    edit_color = '#ADD913';
                    break;
                case "IssueCommentEvent":
                    label_text = event.getActorName() + " commented in " + event.getRepositoryName();
                    edit_color = '#FF4901';
                    break;
                case "ForkEvent":
                    label_text = event.getActorName() + " forked " + event.getRepositoryName();
                    edit_color = '#0184FF';
                    break;
                case "CreateEvent":
                    label_text = event.getActorName() + " created " + event.getRepositoryName();
                    edit_color = '#00C0C0';
                    break;
                case "WatchEvent":
                    label_text = event.getActorName() + " watched " + event.getRepositoryName();
                    edit_color = '#E60062';
                    break;
            }

            let no_label = false;

            size = Math.max(Math.sqrt(Math.abs(size)) * scaleFactor, 3);

            //noinspection JSUnresolvedFunction
            Math.seedrandom(event.getMessage());
            let x = Math.random() * (this.getWidth() - size) + size;
            let y = Math.random() * (this.getHeight() - size) + size;

            let circle_group = this.svg.append('g')
                .attr('transform', 'translate(' + x + ', ' + y + ')')
                .attr('fill', edit_color)
                .style('opacity', 1);

            let ring = circle_group.append('circle');
            ring.attr({r: size, stroke: 'none'});
            ring.transition()
                .attr('r', size + ring_radius)
                .style('opacity', 0)
                .ease(Math.sqrt)
                .duration(ring_anim_duration)
                .remove();

            let circle_container = circle_group.append('a');
            circle_container.attr('xlink:href', event.getUrl());
            circle_container.attr('target', '_blank');
            circle_container.attr('fill', textColor);

            let circle = circle_container.append('circle');
            circle.classed(event.getType(), true);
            circle.attr('r', size)
                .attr('fill', edit_color)
                .transition()
                .duration(maxLife)
                .style('opacity', 0)
                .remove();

            circle_container.on('mouseover', function() {
                circle_container.append('text')
                    .text(label_text)
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

            let text = circle_container.append('text')
                .text(label_text)
                .classed('article-label', true)
                .attr('text-anchor', 'middle')
                .attr('font-size', '0.8em')
                .transition()
                .delay(2000)
                .style('opacity', 0)
                .duration(5000)
                .each(function() { no_label = true; })
                .remove();

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

    EventMessage.prototype.getAction = function() {
        //noinspection JSUnresolvedVariable
        return this.event.action;
    };

    EventMessage.prototype.getRepositoryName = function() {
        //noinspection JSUnresolvedVariable
        return this.event.repoName;
    };

    EventMessage.prototype.getActorName = function() {
        //noinspection JSUnresolvedVariable
        return this.event.actorName;
    };

    EventMessage.prototype.getUrl = function() {
        //noinspection JSUnresolvedVariable
        return this.event.eventUrl;
    };

    EventMessage.prototype.getMessage = function() {
        //noinspection JSUnresolvedVariable
        return this.event.message;
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

        this.connection = new WebSocket(protocol + exports.location.host + '/ws');

        this.connection.addEventListener('message', this.handleMessage.bind(this));
        this.connection.addEventListener('open', this.handleOpen.bind(this));
        this.connection.addEventListener('close', this.handleClose.bind(this));
        this.connection.addEventListener('error', this.handleError.bind(this));
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
        const elements = document.querySelectorAll('.events-remaining-text, .events-remaining-value, .online-users-div');

        for (let i = 0; i < elements.length; i++) {
            elements[i].style.visibility = 'visible';
        }
    };

    Connection.prototype.handleClose = function() {
        this.connection = null;
    };

    Connection.prototype.handleError = function() {
        this.handleClose();

        const reTryInterval = setInterval(function() {
            if (this.connection !== null) {
                clearInterval(reTryInterval);

                return;
            }

            this.start();
        }.bind(this), 5000);
    };

    /**
     * Application
     */
    function Application() {
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
            this.processEvent(this.queue.get());

            document.getElementsByClassName('events-remaining-value')[0].textContent = this.queue.count();

            this.loop();
        }.bind(this), Math.floor(Math.random() * 1000) + 500);
    };

    Application.prototype.processEvent = function(event) {
        if (!event.getMessage()) {
            return;
        }

        if (event.getType() === 'IssuesEvent' || event.getType() === 'IssueCommentEvent') {
            this.audio.playClav(event.getMessage().length * 1.1);
        } else if(event.getType() === 'PushEvent') {
            this.audio.playCelesta(event.getMessage().length * 1.1);
        }else{
            this.audio.playSwell();
        }

        this.gui.drawEvent(event);
    };

    return Application;
}(window, jQuery));

$(function() {
    new GitAmp().run();
});
