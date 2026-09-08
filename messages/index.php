<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();
$user_id = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        u.username,
        u.profile_picture,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON u.role_id = r.role_id
    WHERE u.user_id = ?
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

define(
    'USER_ROLE',
    $currentUser['role_name'] ?? 'Member'
);

require_once '../includes/dashboard/header.php';

$role = strtolower(
    trim($currentUser['role_name'] ?? 'member')
);

if ($role === 'chairperson') {

    require_once
        '../includes/dashboard/sidebars/chairperson.php';

} elseif ($role === 'treasurer') {

    require_once
        '../includes/dashboard/sidebars/treasurer.php';

} elseif ($role === 'secretary') {

    require_once
        '../includes/dashboard/sidebars/secretary.php';

} else {

    require_once
        '../includes/dashboard/sidebars/member.php';
}

require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content clan-messages-page">

    <div class="container-fluid">

        <div class="clan-messages-layout">

<!-- MEMBERS -->

<div class="clan-members-panel">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-users mr-2"></i>
<?= __('family_members') ?>

</h3>

<span
id="onlineCount"
class="badge badge-success float-right"
>
0 <?= __('online') ?>
</span>

</div>

<div
class="card-body p-0"
style="height:650px;overflow-y:auto;"
>

<div
id="onlineMembers"
class="list-group list-group-flush"
></div>

</div>

</div>

</div>


<!-- CHAT -->

<div class="clan-chat-panel">

<div
class="card"
style="height:700px;"
>

<!-- HEADER -->

<div class="card-header">

<div class="d-flex align-items-center">

<div>

<i class="fas fa-comments text-primary mr-2"></i>

<strong>
<?= __('family_group') ?>
</strong>

<div>
<small
class="text-muted"
>
<?= __('family_communication') ?>
</small>
</div>

</div>

<div class="ml-auto">

<button
type="button"
class="btn btn-sm btn-light"
onclick="scrollToBottom()"
>

<i class="fas fa-arrow-down"></i>

</button>

</div>

</div>

</div>


<!-- CHAT -->

<div
id="messagesContainer"
class="card-body"
style="
height:530px;
overflow-y:auto;
background:#f3f5f7;
"
>

<div
id="messageLoading"
class="text-center text-muted"
>

<i class="fas fa-spinner fa-spin"></i>
<?= __('loading_messages') ?>

</div>

</div>


<!-- REPLY -->

<div
id="replyBox"
style="display:none;"
class="border-top bg-light p-2"
>

<div class="d-flex align-items-center">

<i class="fas fa-reply text-primary mr-2"></i>

<div>

<strong id="replyUsername"></strong>

<div
id="replyMessage"
class="small text-muted"
></div>

</div>

<button
type="button"
class="btn btn-sm btn-light ml-auto"
onclick="cancelReply()"
>

<i class="fas fa-times"></i>

</button>

</div>

</div>


<!-- INPUT -->

<div class="card-footer">

<form id="messageForm">

<input
type="hidden"
name="reply_to_message_id"
id="reply_to_message_id"
value=""
>

<div class="input-group">

<textarea
id="messageText"
name="message_text"
class="form-control"
rows="1"
maxlength="5000"
placeholder="<?= __('write_a_message') ?>"
autocomplete="off"
required
></textarea>

<div class="input-group-append">

<button
type="submit"
class="btn btn-primary"
>

<i class="fas fa-paper-plane"></i>

</button>

</div>

</div>

<div
id="mentionSuggestions"
class="list-group"
style="
display:none;
position:absolute;
bottom:55px;
left:0;
width:280px;
max-height:220px;
overflow-y:auto;
z-index:9999;
box-shadow:0 5px 20px rgba(0,0,0,.15);
"
></div>

</form>

</div>

</div>

</div><!-- /.clan-messages-layout -->

</div><!-- /.container-fluid -->

</section><!-- /.clan-messages-page -->


<style>

/* =========================================================
   MESSAGES PAGE - SIDEBAR RESPONSIVENESS FIX
   ========================================================= */

/* Messages page - force responsiveness to sidebar toggle */
.clan-messages-page {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box !important;
    transition: margin-left 0.3s ease-in-out !important;
}

/* When sidebar is open */
body:not(.sidebar-collapse) .clan-messages-page {
    margin-left: 0 !important;
}

/* When sidebar is collapsed */
body.sidebar-collapse .clan-messages-page {
    margin-left: 0 !important;
}

/* Messages container-fluid */
.clan-messages-page .container-fluid {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
    box-sizing: border-box !important;
}

/* Messages layout */
.clan-messages-layout {
    width: 100% !important;
    max-width: 1500px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    box-sizing: border-box !important;
    transition: all 0.3s ease-in-out !important;
}

.mention-item {
    cursor:pointer;
    padding:10px;
}

.mention-item:hover {
    background:#f1f3f5;
}

.mention-avatar {
    width:35px;
    height:35px;
    object-fit:cover;
}

.mention-role {
    font-size:11px;
    color:#6c757d;
}

.mention-highlight {
    color:#007bff;
    font-weight:bold;
}

.family-message {
    display:flex;
    margin-bottom:15px;
}

.family-message.mine {
    justify-content:flex-end;
}

.message-bubble {
    max-width:75%;
    padding:10px 13px;
    border-radius:15px;
    position:relative;
}

.message-other {
    background:white;
    border:1px solid #e4e6e8;
    border-bottom-left-radius:4px;
}

.message-mine {
    background:#007bff;
    color:white;
    border-bottom-right-radius:4px;
}

.message-user {
    font-size:13px;
    font-weight:bold;
    margin-bottom:3px;
}

.message-time {
    font-size:10px;
    opacity:.65;
    margin-top:4px;
    text-align:right;
}

.reply-preview {
    border-left:3px solid #007bff;
    background:rgba(0,0,0,.05);
    padding:5px 8px;
    margin-bottom:7px;
    border-radius:4px;
}

.online-dot {
    width:10px;
    height:10px;
    background:#28a745;
    border-radius:50%;
    display:inline-block;
}

.offline-dot {
    width:10px;
    height:10px;
    background:#adb5bd;
    border-radius:50%;
    display:inline-block;
}

.member-avatar {
    width:42px;
    height:42px;
    object-fit:cover;
}

.reply-button {
    border:0;
    background:transparent;
    font-size:11px;
    opacity:.7;
}

.reply-button:hover {
    opacity:1;
}

/* =========================================================
   CLAN MESSAGES - RESPONSIVE LAYOUT
   ========================================================= */

   .clan-messages-page {
    width: 100%;
    overflow-x: hidden;
}

.clan-messages-page .container-fluid {
    width: 100%;
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

.clan-messages-layout {
    display: grid;
    grid-template-columns: minmax(240px, 280px) minmax(0, 1fr);
    gap: 16px;
    width: 100%;
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
    transition: max-width .3s ease-in-out;
    box-sizing: border-box;
}

/* Family members */
.clan-members-panel {
    flex: 0 0 280px;
    max-width: 280px;
    min-width: 0;
}

.clan-members-panel .card {
    width: 100%;
    margin: 0;
}

/* Chat */
.clan-chat-panel {
    flex: 1 1 auto;
    min-width: 0;
}

.clan-chat-panel .card {
    width: 100%;
    margin: 0;
}

/* Prevent chat from pushing outside content area */
.clan-chat-panel,
.clan-members-panel {
    min-width: 0;
}

/* Messages themselves */
#messagesContainer {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

/* Message bubbles */
.message-bubble {
    max-width: min(75%, 650px);
    word-wrap: break-word;
    overflow-wrap: anywhere;
}

/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 991.98px) {

    .clan-messages-layout {
        gap: 12px;
    }

    .clan-members-panel {
        flex: 0 0 240px;
        max-width: 240px;
    }

    .message-bubble {
        max-width: 85%;
    }
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767.98px) {

    
    .clan-chat-panel {
        width: 100%;
        max-width: 100%;
        flex: none;
    }

    .clan-members-panel .card {
        height: auto !important;
    }

    .clan-members-panel .card-body {
        height: 40vh !important;
    }


    #messagesContainer {
        height: 50vh !important;
    }

    .message-bubble {
        max-width: 88%;
    }
}


/* =========================================================
   SIDEBAR COLLAPSED
   ========================================================= */

body.sidebar-collapse .clan-messages-layout {
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
}


/* =========================================================
   SIDEBAR OPEN
   ========================================================= */

body:not(.sidebar-collapse) .clan-messages-layout {
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
}

/* =========================================================
   CLAN MESSAGES - FINAL LAYOUT FIX
   ========================================================= */

   .clan-messages-page {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box !important;
}

.clan-messages-page .container-fluid {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
    box-sizing: border-box !important;
}


/* =========================================================
   MAIN MESSAGES LAYOUT
   ========================================================= */

.clan-messages-layout {
    position: relative !important;

    display: grid !important;

    grid-template-columns:
        minmax(240px, 280px)
        minmax(0, 1fr) !important;

    gap: 16px !important;

    width: 100% !important;
    max-width: 1500px !important;

    margin-left: auto !important;
    margin-right: auto !important;

    box-sizing: border-box !important;
}


/* =========================================================
   FAMILY MEMBERS PANEL
   ========================================================= */

.clan-members-panel {
    position: static !important;

    left: auto !important;
    right: auto !important;
    top: auto !important;
    bottom: auto !important;

    transform: none !important;

    float: none !important;

    width: 100% !important;
    max-width: 280px !important;
    min-width: 0 !important;

    margin: 0 !important;
    padding: 0 !important;

    box-sizing: border-box !important;

    z-index: 1 !important;
}

.clan-members-panel .card {
    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;

    margin: 0 !important;

    box-sizing: border-box !important;
}


/* =========================================================
   CHAT PANEL
   ========================================================= */

.clan-chat-panel {
    position: static !important;

    left: auto !important;
    right: auto !important;
    top: auto !important;
    bottom: auto !important;

    transform: none !important;

    float: none !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin: 0 !important;
    padding: 0 !important;

    box-sizing: border-box !important;

    z-index: 1 !important;
}

.clan-chat-panel .card {
    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;

    margin: 0 !important;

    box-sizing: border-box !important;
}


/* =========================================================
   CHAT MESSAGE AREA
   ========================================================= */

#messagesContainer {
    width: 100% !important;
    max-width: 100% !important;

    overflow-x: hidden !important;

    box-sizing: border-box !important;
}


/* =========================================================
   MESSAGE BUBBLES
   ========================================================= */

.message-bubble {
    max-width: 75% !important;

    word-break: break-word !important;
    overflow-wrap: anywhere !important;
}


/* =========================================================
   SIDEBAR OPEN
   ========================================================= */

body:not(.sidebar-collapse) .clan-messages-layout {
    width: 100% !important;
    margin-left: auto !important;
    margin-right: auto !important;
}


/* =========================================================
   SIDEBAR COLLAPSED
   ========================================================= */

body.sidebar-collapse .clan-messages-layout {
    width: 100% !important;
    margin-left: auto !important;
    margin-right: auto !important;
}


/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 991.98px) {

    .clan-messages-layout {
        grid-template-columns:
            minmax(210px, 240px)
            minmax(0, 1fr) !important;

        gap: 12px !important;
    }

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767.98px) {

    .clan-messages-layout {
        display: flex !important;

        flex-direction: column !important;

        width: 100% !important;

        gap: 15px !important;
    }

    .clan-members-panel {
        width: 100% !important;
        max-width: 100% !important;
    }

    .clan-chat-panel {
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Drop the fixed 700px inline card height on phones */
    .clan-chat-panel .card {
        height: auto !important;
    }

    /* Keep the composer pinned while scrolling on phones */
    .clan-chat-panel .card-footer {
        position: sticky !important;
        bottom: 0 !important;
        z-index: 5 !important;
        background: #fff !important;
    }

    .message-bubble {
        max-width: 88% !important;
    }

}

/* =========================================================
   MESSAGES PAGE - SIDEBAR RESPONSIVENESS FIX
   ========================================================= */

/* Messages page - force responsiveness to sidebar toggle */
.clan-messages-page {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box !important;
    transition: margin-left 0.3s ease-in-out !important;
}

/* When sidebar is open */
body:not(.sidebar-collapse) .clan-messages-page {
    margin-left: 0 !important;
}

/* When sidebar is collapsed */
body.sidebar-collapse .clan-messages-page {
    margin-left: 0 !important;
}

/* Messages container-fluid */
.clan-messages-page .container-fluid {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
    box-sizing: border-box !important;
}

/* Messages layout */
.clan-messages-layout {
    width: 100% !important;
    max-width: 1500px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    box-sizing: border-box !important;
    transition: all 0.3s ease-in-out !important;
}

</style>


<script>

const CURRENT_USER_ID =
<?= $user_id ?>;

let lastMessageId = 0;

let loading = false;


/*
|--------------------------------------------------------------------------
| Load Messages
|--------------------------------------------------------------------------
*/

function loadMessages() {

    if (loading) return;

    loading = true;

    fetch(
        'fetch.php?last_id='
        + lastMessageId
    )

    .then(response => response.json())

    .then(data => {

        if (!data.success) {
            return;
        }

        const container =
            document.getElementById(
                'messagesContainer'
            );

        const wasAtBottom =
            container.scrollHeight
            - container.scrollTop
            - container.clientHeight
            < 100;


        data.messages.forEach(message => {

            renderMessage(
                message,
                container
            );

            lastMessageId =
                Math.max(
                    lastMessageId,
                    parseInt(
                        message.message_id
                    )
                );

        });


        if (
            wasAtBottom ||
            data.messages.length > 0
        ) {

            scrollToBottom();

        }

    })

    .catch(error => {

        console.error(error);

    })

    .finally(() => {

        loading = false;

        document
            .getElementById(
                'messageLoading'
            )
            .style.display = 'none';

    });

}


/*
|--------------------------------------------------------------------------
| Render Message
|--------------------------------------------------------------------------
*/

function renderMessage(
    message,
    container
) {

    const mine =
        parseInt(message.sender_id)
        === CURRENT_USER_ID;


    const wrapper =
        document.createElement('div');

    wrapper.className =
        mine
        ? 'family-message mine'
        : 'family-message';


    const bubble =
        document.createElement('div');

    bubble.className =
        mine
        ? 'message-bubble message-mine'
        : 'message-bubble message-other';


    let replyHTML = '';


    if (message.reply_message) {

        replyHTML = `

            <div class="reply-preview">

                <strong>
                    ${escapeHTML(
                        message.reply_username
                    )}
                </strong>

                <div class="small">

                    ${escapeHTML(
                        message.reply_message
                    )}

                </div>

            </div>

        `;

    }


    const senderHTML =
        mine
        ? ''
        : `

            <div class="message-user">

                ${escapeHTML(
                    message.username
                )}

            </div>

        `;


    bubble.innerHTML = `

        ${senderHTML}

        ${replyHTML}

        <div>

            ${formatMessage(
                message.message_text
            )}

        </div>

        <div class="message-time">

            ${formatTime(
                message.created_at
            )}

            <button
                type="button"
                class="reply-button ${
                    mine
                    ? 'text-white'
                    : 'text-dark'
                }"
                onclick='replyToMessage(
                    ${JSON.stringify(message)}
                )'
            >

                <i class="fas fa-reply"></i>
                <?= __('reply') ?>

            </button>

            <div class="dropdown" style="display:inline;">
                <button
                    type="button"
                    class="reply-button ${
                        mine
                        ? 'text-white'
                        : 'text-dark'
                    } dropdown-toggle"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >

                    <i class="fas fa-trash"></i>
                    <?= __('delete') ?>

                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="deleteMessage(${message.message_id}, 'me'); return false;">
                        <i class="fas fa-user"></i> <?= __('delete_for_me') ?>
                    </a>
                    ${mine ? `
                    <a class="dropdown-item" href="#" onclick="deleteMessage(${message.message_id}, 'all'); return false;">
                        <i class="fas fa-users"></i> <?= __('delete_for_all') ?>
                    </a>
                    ` : ''}
                </div>
            </div>

        </div>

    `;


    wrapper.appendChild(bubble);

    container.appendChild(wrapper);

}


/*
|--------------------------------------------------------------------------
| Reply
|--------------------------------------------------------------------------
*/

function replyToMessage(message) {

    document
        .getElementById(
            'reply_to_message_id'
        )
        .value =
        message.message_id;


    document
        .getElementById(
            'replyUsername'
        )
        .innerText =
        message.username;


    document
        .getElementById(
            'replyMessage'
        )
        .innerText =
        message.message_text;


    document
        .getElementById(
            'replyBox'
        )
        .style.display =
        'block';


    document
        .getElementById(
            'messageText'
        )
        .focus();

}


function cancelReply() {

    document
        .getElementById(
            'reply_to_message_id'
        )
        .value = '';


    document
        .getElementById(
            'replyBox'
        )
        .style.display =
        'none';

}


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

function deleteMessage(messageId, deleteType) {
    
    if(!confirm(
        deleteType === 'all' 
        ? '<?= __('message_delete_confirm_all') ?>' 
        : '<?= __('message_delete_confirm_me') ?>'
    )) {
        return;
    }

    fetch(
        'delete.php?id=' 
        + messageId 
        + '&type=' 
        + deleteType
    )
    .then(response => response.text())
    .then(data => {
        window.location.reload();
    })
    .catch(error => {
        console.error(error);
        alert('<?= __('failed_to_delete_message') ?>');
    });

}


/*
|--------------------------------------------------------------------------
| Send
|--------------------------------------------------------------------------
*/

document
    .getElementById('messageForm')
    .addEventListener(
        'submit',
        function(e) {

            e.preventDefault();

            const formData =
                new FormData(this);


            fetch(
                'send.php',
                {
                    method:'POST',
                    body:formData
                }
            )

            .then(
                response =>
                    response.json()
            )

            .then(data => {

                if (!data.success) {

                    alert(
                        data.message
                        || '<?= __('failed_to_send_message') ?>'
                    );

                    return;
                }


                document
                    .getElementById(
                        'messageText'
                    )
                    .value = '';


                cancelReply();


                loadMessages();


                scrollToBottom();

            });

        }
    );


/*
|--------------------------------------------------------------------------
| Online Members
|--------------------------------------------------------------------------
*/

function loadOnlineMembers() {

    fetch('online.php')

    .then(
        response =>
            response.json()
    )

    .then(data => {

        if (!data.success) return;


        document
            .getElementById(
                'onlineCount'
            )
            .innerText =
            data.online_count
            + ' <?= __('online') ?>';


        const list =
            document.getElementById(
                'onlineMembers'
            );


        list.innerHTML = '';


        data.members.forEach(member => {

            const item =
                document.createElement(
                    'div'
                );


            item.className =
                'list-group-item';


            const avatar =
                member.profile_picture
                ?
                `
                <img
                    src="<?= BASE_URL ?>uploads/profiles/${escapeHTML(member.profile_picture)}"
                    class="rounded-circle member-avatar mr-2"
                >
                `
                :
                `
                <i
                    class="fas fa-user-circle fa-2x text-secondary mr-2"
                ></i>
                `;


            item.innerHTML = `

                <div class="d-flex align-items-center">

                    ${avatar}

                    <div>

                        <strong>
                            ${escapeHTML(
                                member.username
                            )}
                        </strong>

                        <br>

                        <small>

                            <span
                                class="online-dot mr-1"
                            ></span>

                            <?= __('online') ?>

                        </small>

                    </div>

                </div>

            `;


            list.appendChild(item);

        });

    });

}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function escapeHTML(text) {

    const div =
        document.createElement('div');

    div.textContent =
        text ?? '';

    return div.innerHTML;

}


function formatMessage(text) {

    return escapeHTML(text)

        .replace(
            /@([a-zA-Z0-9_.-]+)/g,
            '<strong>@$1</strong>'
        )

        .replace(
            /\n/g,
            '<br>'
        );

}


function formatTime(date) {

    return new Date(date)
        .toLocaleTimeString(
            [],
            {
                hour:'2-digit',
                minute:'2-digit'
            }
        );

}


function scrollToBottom() {

    const container =
        document.getElementById(
            'messagesContainer'
        );

    container.scrollTop =
        container.scrollHeight;

}


/*
|--------------------------------------------------------------------------
| Enter = Send
| Shift + Enter = New Line
|--------------------------------------------------------------------------
*/

document
    .getElementById(
        'messageText'
    )
    .addEventListener(
        'keydown',
        function(e) {

            if (
                e.key === 'Enter'
                &&
                !e.shiftKey
            ) {

                e.preventDefault();

                document
                    .getElementById(
                        'messageForm'
                    )
                    .dispatchEvent(
                        new Event('submit')
                    );

            }

        }
    );


/*
|--------------------------------------------------------------------------
| Start
|--------------------------------------------------------------------------
*/

loadMessages();

loadOnlineMembers();


setInterval(
    loadMessages,
    2000
);


setInterval(
    loadOnlineMembers,
    5000
);

let mentionTimer = null;


/*
|--------------------------------------------------------------------------
| Detect @
|--------------------------------------------------------------------------
*/

document
    .getElementById('messageText')
    .addEventListener(
        'input',
        function () {

            const text = this.value;

            const cursor =
                this.selectionStart;

            const beforeCursor =
                text.substring(
                    0,
                    cursor
                );


            const match =
                beforeCursor.match(
                    /@([a-zA-Z0-9_.-]*)$/
                );


            if (!match) {

                hideMentionSuggestions();

                return;
            }


            const query =
                match[1];


            clearTimeout(
                mentionTimer
            );


            mentionTimer =
                setTimeout(
                    function () {

                        searchMention(
                            query
                        );

                    },
                    200
                );

        }
    );


/*
|--------------------------------------------------------------------------
| Search Members
|--------------------------------------------------------------------------
*/

function searchMention(query) {

    fetch(
        'mention.php?q='
        + encodeURIComponent(query)
    )

    .then(
        response =>
            response.json()
    )

    .then(data => {

        if (
            !data.success ||
            data.members.length === 0
        ) {

            hideMentionSuggestions();

            return;
        }


        showMentionSuggestions(
            data.members
        );

    })

    .catch(error => {

        console.error(
            'Mention error:',
            error
        );

    });

}


/*
|--------------------------------------------------------------------------
| Show Suggestions
|--------------------------------------------------------------------------
*/

function showMentionSuggestions(
    members
) {

    const box =
        document.getElementById(
            'mentionSuggestions'
        );


    box.innerHTML = '';


    members.forEach(member => {

        const item =
            document.createElement(
                'div'
            );


        item.className =
            'mention-item';


        const avatar =
            member.profile_picture
            ?
            `
            <img
                src="<?= BASE_URL ?>uploads/profiles/${escapeHTML(member.profile_picture)}"
                class="rounded-circle mention-avatar mr-2"
            >
            `
            :
            `
            <i
                class="fas fa-user-circle fa-2x text-secondary mr-2"
            ></i>
            `;


        item.innerHTML = `

            <div
                class="d-flex align-items-center"
            >

                ${avatar}

                <div>

                    <strong>
                        @${escapeHTML(
                            member.username
                        )}
                    </strong>

                    <div
                        class="mention-role"
                    >

                        ${escapeHTML(
                            member.role_name
                            || '<?= __('member') ?>'
                        )}

                    </div>

                </div>

            </div>

        `;


        item.addEventListener(
            'click',
            function () {

                insertMention(
                    member.username
                );

            }
        );


        box.appendChild(item);

    });


    box.style.display =
        'block';

}


/*
|--------------------------------------------------------------------------
| Insert Selected Mention
|--------------------------------------------------------------------------
*/

function insertMention(
    username
) {

    const textarea =
        document.getElementById(
            'messageText'
        );


    const text =
        textarea.value;


    const cursor =
        textarea.selectionStart;


    const beforeCursor =
        text.substring(
            0,
            cursor
        );


    const afterCursor =
        text.substring(
            cursor
        );


    const replaced =
        beforeCursor.replace(
            /@([a-zA-Z0-9_.-]*)$/,
            '@' + username + ' '
        );


    textarea.value =
        replaced + afterCursor;


    const newPosition =
        replaced.length;


    textarea.focus();


    textarea.setSelectionRange(
        newPosition,
        newPosition
    );


    hideMentionSuggestions();

}


/*
|--------------------------------------------------------------------------
| Hide
|--------------------------------------------------------------------------
*/

function hideMentionSuggestions() {

    const box =
        document.getElementById(
            'mentionSuggestions'
        );

    box.style.display =
        'none';

    box.innerHTML = '';

}


/*
|--------------------------------------------------------------------------
| Escape
|--------------------------------------------------------------------------
*/

function escapeHTML(text) {

    const div =
        document.createElement('div');

    div.textContent =
        text ?? '';

    return div.innerHTML;

}

</script>

</div>

</div>

<?php

require_once '../includes/dashboard/footer.php';

?>