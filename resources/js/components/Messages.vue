<template>
    <div class="row position-relative" style="height: 85vh">
        <div class="col-md-4 col-lg-3 h-100">
            <b-card header-class="p-1" body-class="h-100 p-0 overflow-auto">
                <template v-slot:header>
                    <b-input-group>
                        <b-form-input @input="getInboxContacts(null,true)"
                                      v-model="search_users"
                                      type="search"
                                      placeholder="Search Conversations..."></b-form-input>
                        <template v-slot:append>
                            <b-button title="New Conversation" v-b-modal.addConversation>
                                <i class="fa fa-plus"></i>
                            </b-button>
                        </template>
                    </b-input-group>
                </template>
                <b-list-group style="max-height: 80vh" class="contacts">
                    <b-list-group-item
                        class="border-right-0 border-left-0"
                        @click="current_user=contact"
                        v-for="(contact,ck) in contacts"
                        :key="ck"
                        exact exact-active-class="active"
                        :to="{name:'Messages',params:{user_id:contact.id}}">
                        {{contact.name}}
                    </b-list-group-item>
                    <b-list-group-item button class="text-center"
                                       @click="getInboxContacts(contacts_next_page_url)"
                                       variant="dark"
                                       v-if="contacts_next_page_url">
                        <i class="fa fa-forward"></i>
                        load more...
                    </b-list-group-item>
                </b-list-group>
            </b-card>
        </div>
        <div class="col-md-8 col-lg-9 h-100">
            <b-card class="h-100" footer-class="p-2" body-class="h-100 p-1 overflow-auto messages_holder">
                <template v-slot:header>
                    <div class="row">
                        <div class="col">
                            {{current_user?(current_user.name +' ( '+current_user.email+' ) '):user_name_email}}
                        </div>
                        <div class="col text-right" @click="getConversation($route.params.user_id,true)">
                            <b-form-checkbox v-model="message_fetch_auto"
                                             switch inline
                                             @input="message_fetch_auto? setMessagesRefresh(): clearMessagesRefresh()">
                                Auto Refresh
                            </b-form-checkbox>
                            <input type="number" style="max-width: 100px"
                                   v-model="refresh_interval" :min="0"
                                   @change="setMessagesRefresh"
                                   v-if="message_fetch_auto">
                            <b-button size="sm">Refresh</b-button>
                        </div>
                    </div>
                </template>
                <b-list-group>
                    <b-list-group-item v-if="messages_next_page_url"
                                       class="text-center"
                                       button
                                       @click="getConversation($route.params.user_id,false,messages_next_page_url)"
                                       title="Click here to load older messages">
                        {{message_loading}}
                    </b-list-group-item>
                    <b-list-group-item class="border-0"
                                       style="border:1px solid lightgray;"
                                       v-for="(message,mkey) in getMessages()"
                                       :key="mkey">
                        <h6 :class="{'text-right':Number(auth_id)===Number(message.sender_id)}"
                            class="text-muted font-weight-bold">{{message.sender.name}}</h6>
                        <div :class="{'text-right':Number(auth_id)===Number(message.sender_id)}">
                            <!--                            :class="{'bg-dark text-white':Number(auth_id)!==Number(message.sender_id)}"-->
                            <p class="bg-light d-inline-block px-lg-3 px-md-3 px-sm-5 py-2"
                               style="min-width: 100px;max-width: 80%;border-radius: 20px;"
                               v-html="autolink(message.message,{ target: '_blank'})"></p>
                        </div>

                        <div class="small text-muted font-italic"
                             :class="{'text-right':Number(auth_id)===Number(message.sender_id)}">
                            {{message.created_at |dayjs}}
                        </div>
                    </b-list-group-item>
                </b-list-group>

                <template v-slot:footer>
                    <b-form-textarea @keypress.ctrl.enter="sendMessage()"
                                     title="Press CTRL+Enter to Send the Message"
                                     v-model="message"
                                     placeholder="Write the Message Here... Press CTRL+Enter to Send the Message"></b-form-textarea>
                </template>
            </b-card>
        </div>
        <b-modal id="addConversation"
                 header-bg-variant="dark"
                 header-text-variant="light"
                 size="lg"
                 title="New Conversation"
                 @hidden="()=>{send_to=null;message=null;}">
            <form
                @submit.prevent="sendMessage(send_to,message,(res)=>{
                    $bvModal.hide('addConversation');
                    search_users_typehead=null;
                    getInboxContacts( null, true);
                    // $log(res.data.data, $router.resolve({name:'Messages',params:{user_id:res.data.receiver_id}}))
                    $router.push({name:'Messages',params:{user_id:res.data.data.receiver_id}});
                })">
                <type-head :search="search_users_typehead"
                           :search-items="searchItems"
                           autocomplete="off"
                           placeholder="Search Users"
                           @selected="item=>send_to=item.id">
                    <template v-slot:option="item">
                        {{item.id}} # {{item.name}} | {{item.email}}
                    </template>
                </type-head>
                <b-form-group label="Message">
                    <b-form-textarea v-model="message" placeholder="Say Hi! to"></b-form-textarea>
                </b-form-group>
                <button type="submit" ref="add_conversion_submit" class="d-none">SUBMIT</button>
            </form>
            <template v-slot:modal-footer="{close}">
                <b-button variant="primary" @click="()=>{$refs.add_conversion_submit.click()}">Send</b-button>
                <b-button @click="close()">Close</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import {msgBox} from "@/partials/datatable";
    import autolink from "./../partials/autolink"
    import TypeHead from "../partials/TypeHead";
    import Pusher from "pusher-js";

    export default {
        name: "Messages",
        components: {
            TypeHead
        },
        props: {
            users_search_url: {
                type: String,
                default: "/backend/users/search"
            }
        },
        data() {
            return {
                current_user: null,
                user_name_email: null,
                contacts: [],
                messages: [],
                message: null,
                auth_id: null,
                contacts_next_page_url: null,
                messages_next_page_url: null,
                message_loading: 'load more...',
                message_fetch_auto: false,
                refresh_interval: 3, //seconds,
                interval: null,
                search_users: null,
                search_users_typehead: null,
                send_to: null
            }
        },
        mounted() {
            this.getInboxContacts();
            if (this.$route.params.user_id) {
                this.getConversation(this.$route.params.user_id);
            }
            window.Echo
                .private('my-channel')
                // .listen('.pusher:subscription_succeeded', e => console.log(("Channel Subscribed")))
                .listen('.NewMessage', (e) => {
                    if (Number(this.$route.params.user_id) === Number(e.message.sender_id)) {
                        this.messages.push(e.message);
                        setTimeout(() => this.scrollBottom(), 10);
                    }
                });
        },
        beforeRouteUpdate(to, from, next) {
            this.messages = [];
            this.messages_next_page_url = null;
            this.getConversation(to.params.user_id);
            next();
        },
        methods: {
            msgBox, autolink,
            setMessagesRefresh() {
                if (this.interval) {
                    clearInterval(this.interval);
                }
                this.interval = setInterval(() => {
                    // console.log("Gotcha")
                    this.getConversation(this.$route.params.user_id, true);
                }, this.refresh_interval * 1000);
            },
            clearMessagesRefresh() {
                if (this.interval) {
                    clearInterval(this.interval);
                }
            },
            getMessages() {
                return this.messages.slice().sort((a, b) => (a.id > b.id) ? 1 : -1)
            },
            scrollBottom(bottom = true) {
                let el = document.querySelector(".messages_holder");
                el.scrollTop = bottom ? el.scrollHeight : 0;
            },

            sendMessage(send_to = null, message = null, cb = null) {
                if (message || this.message) {
                    axios.post("/backend/LaravelMessenger/store", {
                        message: message || this.message,
                        send_to: send_to || this.$route.params.user_id
                    }).then(res => {
                        if (res.data.status) {
                            this.messages.push(res.data.data);
                            this.message = null;
                            setTimeout(() => this.scrollBottom(), 10);
                        } else {
                            this.msgBox(res.data);
                        }
                        if (typeof cb === "function") {
                            cb(res);
                        }
                        // console.log(res)
                    }).catch(err => {
                        this.msgBox(err.response.data);
                        console.log(err.response)
                    });
                }
            },
            last(array, n) {
                if (array == null)
                    return void 0;
                if (n == null)
                    return array[array.length - 1];
                return array.slice(Math.max(array.length - n, 0));
            },
            getUser(user_id) {
                axios.post("/backend/LaravelMessenger/getUser", {
                    user_id: user_id
                }).then(res => {
                    this.contacts = [res.data, ...this.contacts];
                }).catch(err => {
                    console.log(err.response);
                });
            },
            getConversation(user_id, refresh = false, url = null) {
                if (!user_id) {
                    return false;
                }
                this.message_loading = "updating conversation...";
                axios.post(url || "/backend/LaravelMessenger/conversation", {user_id})
                    .then(res => {
                        // console.log(res.headers.auth_id)
                        //check if same last id
                        if (this.messages.length && this.last(res.data.data).id === this.last(this.messages).id) {
                            return false;
                        }
                        if (refresh) {
                            this.messages = res.data.data;
                        } else {
                            res.data.data.forEach(item => this.messages.push(item));
                        }

                        this.auth_id = res.headers.auth_id;
                        this.user_name_email = res.headers.user_name_email;
                        this.messages_next_page_url = res.data.next_page_url;
                        this.message_loading = "load more...";
                        if (!this.contacts.map(i => Number(i.id)).includes(Number(this.$route.params.user_id))) {
                            this.getUser(this.$route.params.user_id);
                        }
                        setTimeout(() => this.scrollBottom(!url), 10);
                    })
                    .catch(err => {
                        this.messages = [];
                        this.auth_id = null;
                        this.message_loading = "load more...";
                        console.log(err.response);
                    });
            },
            getInboxContacts(url = null, reset = false) {
                axios.post(url || "/backend/LaravelMessenger/inboxContacts", {
                    query: this.search_users
                }).then(res => {
                    if (reset) {
                        this.contacts = res.data.data;
                    } else {
                        res.data.data.forEach(item => this.contacts.push(item));
                    }
                    this.contacts_next_page_url = res.data.next_page_url
                    // console.log(res);
                }).catch(err => {
                    this.contacts = [];
                    console.log(err.response);
                });
            },
            searchItems(search) {
                if (!search) {
                    this.users = [];
                    return false;
                }
                return axios.post(this.users_search_url, {
                    search: search
                });
            },
            selectUser(user) {
                console.log(user)
                this.search = '';
            }
        }
    }
</script>

<style lang="scss" scoped>
    .messages_holder, .contacts {

    }

</style>
