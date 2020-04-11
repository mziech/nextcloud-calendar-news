$(function () {
    var $frame = $('.calendar-news-simple iframe'),
        $links = $('.calendar-news-simple .frame-link'),
        $loading = $('.calendar-news-simple .loading');
    $links.click(function () {
        var $this = $(this);
        $links.closest("li").removeClass("active");
        $this.closest("li").addClass("active");
        $frame.hide();
        $loading.show();
        $.get($this.attr("href")).then(function (data) {
            $frame.show();
            $loading.hide();
            var doc = $frame.get(0).contentWindow.document.open();
            doc.write(data);
            doc.close();
        });
        return false;
    });

    $links.first().click();
});