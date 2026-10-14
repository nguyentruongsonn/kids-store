/**
 * Trang cài đặt payOS: hiện/ẩn khoá, sao chép webhook URL.
 */
function payosToggleKey(inputId, buttonId) {
  var input = document.getElementById(inputId);
  var button = document.getElementById(buttonId);
  if (!input || !button) {
    return;
  }
  var show = input.type === "password";
  input.type = show ? "text" : "password";
  button.value = show ? payos_data.hide : payos_data.show;
}

function showClientId() {
  payosToggleKey("payos_client_id", "show_client_id");
}

function showApiKey() {
  payosToggleKey("payos_api_key", "show_api_key");
}

function showChecksumKey() {
  payosToggleKey("payos_checksum_key", "show_checksum_key");
}

document.addEventListener("click", function (event) {
  var button = event.target.closest ? event.target.closest("[data-payos-copy]") : null;
  if (!button) {
    return;
  }
  var input = document.getElementById(button.getAttribute("data-payos-copy"));
  if (!input) {
    return;
  }
  var label = button.textContent;
  var done = function () {
    button.textContent = button.getAttribute("data-copied") || label;
    setTimeout(function () {
      button.textContent = label;
    }, 1500);
  };
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(input.value).then(done);
  } else {
    input.select();
    document.execCommand("copy");
    done();
  }
});
