// ============================================================
// js/chat-widget.js
// Widget chat pelanggan <-> CS. Di-include di semua halaman
// pelanggan (shop.php, about.php, contact.php, riwayat_saya.php).
// Membutuhkan: elemen kosong <div id="chatWidgetRoot"></div>
// dan variabel global window.CHAT_USER_NAME (opsional, untuk avatar).
// ============================================================

(function () {
    let lastChatId = 0;
    let pollInterval = null;
    let isOpen = false;

    function buildWidget() {
        const root = document.getElementById('chatWidgetRoot');
        if (!root) return;

        root.innerHTML = `
        <div id="chatToggleBtn" style="position:fixed;bottom:20px;right:20px;width:56px;height:56px;border-radius:50%;background:#059669;box-shadow:0 8px 24px rgba(5,150,105,0.35);display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:9980;transition:transform .15s ease;">
          <iconify-icon icon="solar:chat-round-dots-bold" width="26" style="color:white;"></iconify-icon>
          <span id="chatToggleBadge" style="display:none;position:absolute;top:-4px;right:-4px;min-width:20px;height:20px;padding:0 4px;background:#ef4444;color:white;font-size:11px;font-weight:700;border-radius:10px;align-items:center;justify-content:center;display:none;">0</span>
        </div>

        <div id="chatPanel" style="display:none;position:fixed;bottom:88px;right:20px;width:340px;max-width:calc(100vw - 32px);height:460px;max-height:calc(100vh - 140px);background:white;border-radius:20px;box-shadow:0 16px 48px rgba(0,0,0,0.18);z-index:9981;flex-direction:column;overflow:hidden;border:1px solid rgba(0,0,0,0.06);">
          <div style="background:linear-gradient(135deg,#10b981,#059669);padding:14px 16px;display:flex;align-items:center;justify-content:between;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;flex:1;">
              <div style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                <iconify-icon icon="solar:headphones-round-bold" width="18" style="color:white;"></iconify-icon>
              </div>
              <div>
                <p style="color:white;font-weight:700;font-size:13px;margin:0;">Customer Service</p>
                <p style="color:rgba(255,255,255,0.8);font-size:11px;margin:0;">FIRAJAYA</p>
              </div>
            </div>
            <button id="chatCloseBtn" style="background:rgba(255,255,255,0.15);border:none;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
              <iconify-icon icon="solar:close-circle-linear" width="18" style="color:white;"></iconify-icon>
            </button>
          </div>
          <div id="chatMessages" style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px;background:#f9fafb;"></div>
          <div style="padding:10px;border-top:1px solid #f0f0f0;display:flex;gap:8px;background:white;">
            <input id="chatInput" type="text" placeholder="Tulis pesan..." style="flex:1;padding:10px 14px;border-radius:12px;border:1px solid #e5e5e5;font-size:13px;outline:none;" />
            <button id="chatSendBtn" style="width:40px;height:40px;border-radius:12px;background:#059669;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">
              <iconify-icon icon="solar:send-bold" width="18" style="color:white;"></iconify-icon>
            </button>
          </div>
        </div>`;

        document.getElementById('chatToggleBtn').addEventListener('click', toggleChat);
        document.getElementById('chatCloseBtn').addEventListener('click', toggleChat);
        document.getElementById('chatSendBtn').addEventListener('click', sendMessage);
        document.getElementById('chatInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    function toggleChat() {
        isOpen = !isOpen;
        document.getElementById('chatPanel').style.display = isOpen ? 'flex' : 'none';
        if (isOpen) {
            loadMessages(true);
            document.getElementById('chatInput').focus();
        }
    }

    function renderMessage(msg) {
        const isMe = msg.pengirim === 'user';
        const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const bubble = document.createElement('div');
        bubble.style.cssText = `max-width:78%;align-self:${isMe ? 'flex-end' : 'flex-start'};`;
        bubble.innerHTML = `
          <div style="background:${isMe ? '#059669' : 'white'};color:${isMe ? 'white' : '#262626'};padding:9px 12px;border-radius:14px;font-size:13px;line-height:1.4;box-shadow:0 1px 2px rgba(0,0,0,0.06);word-wrap:break-word;">${escapeHtml(msg.isi_pesan)}</div>
          <div style="font-size:10px;color:#a3a3a3;margin-top:3px;text-align:${isMe ? 'right' : 'left'};">${time}</div>
        `;
        return bubble;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    async function loadMessages(scrollToBottom) {
        try {
            const markRead = isOpen ? '1' : '0';
            const res = await fetch(`/api/get_chat_messages.php?since_id=${lastChatId}&mark_read=${markRead}&_t=${Date.now()}`);
            const json = await res.json();
            if (!json.success) return;

            const container = document.getElementById('chatMessages');
            if (json.data.length === 0 && lastChatId === 0) {
                container.innerHTML = `<div style="text-align:center;color:#a3a3a3;font-size:12px;padding:20px;">Mulai percakapan dengan CS kami 👋</div>`;
            }
            json.data.forEach(msg => {
                if (lastChatId === 0 && container.children.length === 1 && container.children[0].textContent.includes('Mulai percakapan')) {
                    container.innerHTML = '';
                }
                container.appendChild(renderMessage(msg));
                lastChatId = Math.max(lastChatId, msg.id_chat);
            });
            if (scrollToBottom || json.data.length > 0) {
                container.scrollTop = container.scrollHeight;
            }
            updateBadge();
        } catch (e) { /* diam saja, coba lagi di polling berikutnya */ }
    }

    async function sendMessage() {
        const input = document.getElementById('chatInput');
        const isi_pesan = input.value.trim();
        if (isi_pesan === '') return;

        input.value = '';
        try {
            const res = await fetch('/api/send_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ isi_pesan })
            });
            const json = await res.json();
            if (json.success) {
                loadMessages(true);
            }
        } catch (e) {
            input.value = isi_pesan; // kembalikan teks kalau gagal kirim
        }
    }

    async function updateBadge() {
        try {
            const res = await fetch('/api/get_chat_unread_count.php?_t=' + Date.now());
            const json = await res.json();
            const badge = document.getElementById('chatToggleBadge');
            if (json.success && json.unread_count > 0 && !isOpen) {
                badge.textContent = json.unread_count > 9 ? '9+' : json.unread_count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        } catch (e) { /* abaikan */ }
    }

    function init() {
        buildWidget();
        loadMessages(false);
        pollInterval = setInterval(() => loadMessages(isOpen), 4000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();