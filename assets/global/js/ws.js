document.addEventListener("DOMContentLoaded", () => {
  if (typeof window.liveTvConfig === "undefined") {
    console.error("Live TV config is not defined.");
    return;
  }
  setupWebSocketConnection();
  initializeCommentHandling();
  loadExistingComments();
  setupEmojiPicker();
});

let socket;
const MAX_RETRIES = 20;
let retryCount = 0;

function setupWebSocketConnection() {
  const { liveTvId } = window.liveTvConfig;

  const connectionOverlay = document.querySelector(".connection-overlay");
  const showConnectionOverlay = () => connectionOverlay.classList.add("active");
  const hideConnectionOverlay = () =>
    connectionOverlay.classList.remove("active");

  if (!liveTvId) {
    showConnectionOverlay();
    console.error("Live TV ID not found!");
    return;
  }

  const socketUrl = window.liveTvConfig.socketUri;

  if (!socketUrl) {
    showConnectionOverlay();
    console.error("Socket URL not found!");
    return;
  }

  function connect() {
    socket = new WebSocket(socketUrl);

    socket.onopen = () => {
      hideConnectionOverlay();
      retryCount = 0;
      socket.send(JSON.stringify({ type: "join-live-tv", liveTvId }));
    };

    socket.onmessage = (event) => {
      const data = JSON.parse(event.data);
      if (data.type === "comment-received") {
        appendComment(data);
      }
    };

    socket.onclose = () => {
      showConnectionOverlay();
      console.warn("⚠️ WebSocket Disconnected");
      if (retryCount < MAX_RETRIES) {
        setTimeout(() => {
          retryCount++;
          connect();
        }, 3000);
      } else {
        console.error("❌ Max reconnection attempts reached.");
      }
    };

    socket.onerror = (error) => {
      console.error("❌ WebSocket Error:", error);
      socket.close();
    };
  }

  connect();
  window.socket = socket;
}

function initializeCommentHandling() {
  const form = document.getElementById("live-tv-comment-form");
  const commentInput = document.getElementById("live-tv-comment-input");

  if (!form || !commentInput) {
    console.warn("Comment form elements not found!");
    return;
  }

  commentInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      e.shiftKey ? insertNewLine(commentInput) : submitComment();
    }
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    submitComment();
  });
}

function submitComment() {
  const { liveTvId, commentStoreRoute, csrfToken } = window.liveTvConfig;
  const commentInput = document.getElementById("live-tv-comment-input");
  const comment = commentInput.value.trim();

  if (!comment) return;
  fetch(commentStoreRoute, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({ live_id: liveTvId, comment }),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        commentInput.value = "";
        window.socket.send(
          JSON.stringify({
            type: "new-comment",
            liveTvId,
            user: {
              fullname: result.comment.user.firstname,
              imageUrl: result.comment.user.imageUrl,
            },
            comment,
          })
        );
      }
    })
    .catch((error) => {
      console.error("❌ Error submitting comment:", error);
    });
}

async function loadExistingComments() {
  try {
    const response = await fetch(window.liveTvConfig.commentGetRoute);
    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

    const comments = await response.json();
    comments.reverse().forEach((comment) =>
      appendComment({
        user: {
          fullname: comment.user.firstname,
          imageUrl: comment.user.imageUrl,
        },
        comment: comment.comment,
      })
    );

    chatBodyScroll();
  } catch (error) {
    console.error("❌ Error loading comments:", error);
  }
}

function appendComment(data) {
  const commentContainer = document.getElementById(
    "live-tv-comments-container"
  );

  const commentElement = document.createElement("div");
  commentElement.classList.add("message-item");
  commentElement.innerHTML = `
      <div class="message-item__wrapper">
          <div class="message-item__profile">
              <img src="${data.user.imageUrl}" alt="avatar">
          </div>
          <div class="comment-content">
              <span class="user__name">${data.user.fullname}</span>
              <small class="message-item__text">${data.comment}</small>
          </div>
      </div>`;
  commentContainer.appendChild(commentElement);

  chatBodyScroll();
}

function chatBodyScroll() {
  setTimeout(() => {
    const chatBody = document.querySelector(".chat__body");
    chatBody.scrollTop = chatBody.scrollHeight;
  }, 100);
}

window.addEventListener("load", chatBodyScroll);

function setupEmojiPicker() {
  const picker = new EmojiButton();

  const button = document.getElementById("emoji-button");

  button.addEventListener("click", () => picker.togglePicker(button));
  picker.on("emoji", (emoji) => {
    const input = document.getElementById("live-tv-comment-input");
    input.value += emoji;
  });
}
