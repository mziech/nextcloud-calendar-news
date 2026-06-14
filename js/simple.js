const $wrapper = document.getElementById("app-content-wrapper"),
    $frame = document.querySelector('.calendar-news-simple iframe'),
    $links = document.querySelectorAll('.calendar-news-simple .frame-link');

function loadPreview($this) {
    $links.forEach($link => $link.classList.remove("active"));
    $this.classList.add("active");
    $wrapper.classList.add("loading");
    fetch($this.getAttribute("href")).then(res => res.text()).then(function (data) {
        $wrapper.classList.remove("loading");
        const doc = $frame.contentWindow.document.open();
        doc.write(data);
        doc.close();
    }).catch(err => {
        console.error("Error while loading preview", err);
        OCP.Toast.error(t("majordomo", "Error while loading preview"));
    });
}

$links.forEach($link => $link.addEventListener("click", event => {
    event.preventDefault();
    loadPreview($link);
    return false;
}));

loadPreview($links.item(0));
