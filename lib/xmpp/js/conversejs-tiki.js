
(function(window, converse) {
    var _converse;

    converse.plugins.add("tiki", {
        "dependencies": [
            'converse-muc-views',
            'converse-controlbox',
            'converse-chatview',
            'converse-bookmarks',
        ],

        "initialize": function () {
            _converse = this._converse;
            var error = console && console.error
                ? console.error.bind(console)
                : function (msg) { return feedback(msg, "error", false); };
            var tr = window.tr
                ? tr
                : function(str) { return str; };

            if (_converse.settings.authentication === "anonymous") {
                _converse.promises.initialized.then(function() {
                    delete _converse.connection.mechanisms.ANONYMOUS.priority;
                    _converse.connection.mechanisms.ANONYMOUS.priority = 50;
                });
            }

            /**
             * This missing method causing errors
             */
            converse.env.Strophe.Connection.prototype.setProtocol = function() {
                const proto = this.options.protocol || "";

                if (this.options.worker) {
                this._proto = new Strophe.WorkerWebsocket(this);
                } else if (this.service.indexOf("ws:") === 0 || this.service.indexOf("wss:") === 0 || proto.indexOf("ws") === 0) {
                this._proto = new Strophe.Websocket(this);
                } else {
                this._proto = new Strophe.Bosh(this);
                }
            };

            _converse.api.listen.on("noResumeableSession", function (xhr) {
                error(tr("XMPP Module error") + ": " + xhr.statusText);
                $("#conversejs").fadeOut("fast");
            });
        },

        "overrides":{
            "Bookmarks": {
                "openBookmarkedRoom":  async function (bookmark) {
                    if (bookmark.get('autojoin')) {
                        const groupchat = await _converse.api.rooms.create(bookmark.get('jid'), bookmark.get('nick'));
                        if (!(groupchat.get('hidden') || groupchat.get('minimized'))) {
                            groupchat.trigger('show');
                        }
                    }
                    return bookmark;
                },
            },
            "ChatRoomView": {
                "initialize": function () {
                    this.model.set('minimized', this.is_chatroom === true);
                    this.__super__.initialize.apply(this, arguments);
                },
                "createOccupantsView": function() {
                    var show_occupants = _converse.user_settings.show_occupants_by_default;

                    if (show_occupants === undefined || show_occupants === null) {
                        show_occupants = window.innerWidth > 576;
                    }

                    this.model.occupants.chatroomview = this;
                    this.occupantsview = new _converse.ChatRoomOccupantsView({
                        'model': this.model.occupants
                    });

                    this.model.save({
                        'hidden_occupants': ! show_occupants
                    });

                    const container_el = this.el.querySelector('.chatroom-body');
                    container_el.insertAdjacentElement('beforeend', this.occupantsview.el);

                    return this;
                },
            },
            "ChatBoxes": {
                "chatBoxMayBeShown": function(chatbox) {
                    if (chatbox.get('id') === 'controlbox') {
                        return _converse.show_controlbox_by_default === true
                            && window.innerWidth >= 1024
                            && window.innerHeight >= 768;
                    }
                    return this.__super__.chatBoxMayBeShown(chatbox);
                }
            },
            "ChatBoxView":{
                "renderMessageForm": function() {
                    this.__super__.renderMessageForm();

                    var form_container = this.el.querySelector('.message-form-container');
                    var nick_reset_box = this.el.querySelector('.tiki-reset-box');
                    var object = window.sessionStorage.getItem(_converse.jid);

                    if (object) {
                        object = JSON.parse(object);
                    }
                    var nickSet = object ? object.nickSet : null;

                    if (form_container && _converse.authentication === "anonymous" && !nickSet && !nick_reset_box) {
                        form_container.classList.add('hidden');
                        this.renderNickResetBox(form_container.parentElement);
                        return this;
                    }

                    return this;
                },

                "onNickReset": function(evt, nick) {

                    if(!(typeof nick === "string" || (nick instanceof String))) {
                        return;
                    }

                    nick = nick.trim().replace(/\s+/g, ' ');
                    if (!nick.length) {
                        return;
                    }

                    var object = window.sessionStorage.getItem(_converse.jid);
                    object = object || {};
                    object.nickSet = nick;

                    try {
                        this.parseMessageForCommands('/nick ' + nick);
                        window.sessionStorage.setItem(_converse.jid, JSON.stringify(object));
                    } catch(e) {
                        console.error("Can't change nickname:", e);
                    }
                    var message_box = this.el.querySelector('.message-form-container.hidden');
                    var nick_reset_box = this.el.querySelector('.tiki-reset-box');

                    nick_reset_box.classList.add('hidden');
                    message_box.classList.remove('hidden');
                },

                "renderNickResetBox": function (container) {
                    var resetBox = document.createElement('div');
                    resetBox.classList.add('tiki-reset-box', 'form-row', 'px-1', 'py-1', 'bg-secondary');

                    var col = document.createElement('div');
                    col.classList.add('col');
                    resetBox.appendChild(col);

                    var input = document.createElement('input');
                    input.type="text";
                    input.placeholder="Type a nick";
                    input.classList.add('form-control');
                    col.appendChild(input);

                    col = document.createElement('div');
                    col.classList.add('col', 'btn-group');
                    resetBox.appendChild(col);

                    var button = document.createElement('button');
                    button.innerText = 'Join';
                    button.classList.add('btn', 'btn-info');
                    col.appendChild(button);

                    var onNickReset = this.onNickReset.bind(this);
                    button.addEventListener("click", function (evt) {
                        return onNickReset(evt, input.value);
                    });
                    input.addEventListener("keydown", function(evt){
                        return evt.keyCode === 13 && onNickReset(evt, input.value);
                    });

                    button = document.createElement('a');
                    button.href = 'tiki-register.php';
                    button.innerText = 'Register';
                    button.classList.add('btn', 'btn-success', 'text-white');
                    col.appendChild(button);

                    container.appendChild(resetBox);
                    return this;
                },
            }
        }

    });
})(window, window.converse);