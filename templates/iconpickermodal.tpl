
{*debut modal icon picker *}
<div id="iconPicker" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{tr}Close{/tr}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Icon Picker</h4>
            </div>
            <div class="modal-body">
                <div>
                    <ul class="icon-picker-list">
                        <li>
                            <a data-class="#item# #activeState#" data-index="#index#">
                                <span class="#item#"></span>
                                <span class="name-class">#item#</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="change-icon" class="btn btn-success">
                    {icon name="check-circle"}
                    {tr}Use Selected Icon{/tr}
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

{* fin modal icon picker*}
{literal}
    <script>

        /*demo icon picker scrits*/

        var icons = [{ icon: 'fas fa-glass' }, { icon: 'fas fa-music' }, { icon: 'fas fa-search' }, { icon: 'fas fa-envelope-o' }, { icon: 'fas fa-heart' }, { icon: 'fas fa-star' }, { icon: 'fas fa-star-o' }, { icon: 'fas fa-user' }, { icon: 'fas fa-film' }, { icon: 'fas fa-th-large' }, { icon: 'fas fa-th' }, { icon: 'fas fa-th-list' }, { icon: 'fas fa-check' }, { icon: 'fas fa-times' }, { icon: 'fas fa-search-plus' }, { icon: 'fas fa-search-minus' }, { icon: 'fas fa-power-off' }, { icon: 'fas fa-signal' }, { icon: 'fas fa-cog' }, { icon: 'fas fa-trash-o' }, { icon: 'fas fa-home' }, { icon: 'fas fa-file-o' }, { icon: 'fas fa-clock-o' }, { icon: 'fas fa-road' }, { icon: 'fas fa-download' }, { icon: 'fas fa-arrow-circle-o-down' }, { icon: 'fas fa-arrow-circle-o-up' }, { icon: 'fas fa-inbox' }, { icon: 'fas fa-play-circle-o' }, { icon: 'fas fa-repeat' }, { icon: 'fas fa-refresh' }, { icon: 'fas fa-list-alt' }, { icon: 'fas fa-lock' }, { icon: 'fas fa-flag' }, { icon: 'fas fa-headphones' }, { icon: 'fas fa-volume-off' }, { icon: 'fas fa-volume-down' }, { icon: 'fas fa-volume-up' }, { icon: 'fas fa-qrcode' }, { icon: 'fas fa-barcode' }, { icon: 'fas fa-tag' }, { icon: 'fas fa-tags' }, { icon: 'fas fa-book' }, { icon: 'fas fa-bookmark' }, { icon: 'fas fa-print' }, { icon: 'fas fa-camera' }, { icon: 'fas fa-font' }, { icon: 'fas fa-bold' }, { icon: 'fas fa-italic' }, { icon: 'fas fa-text-height' }, { icon: 'fas fa-text-width' }, { icon: 'fas fa-align-left' }, { icon: 'fas fa-align-center' }, { icon: 'fas fa-align-right' }, { icon: 'fas fa-align-justify' }, { icon: 'fas fa-list' }, { icon: 'fas fa-outdent' }, { icon: 'fas fa-indent' }, { icon: 'fas fa-video-camera' }, { icon: 'fas fa-picture-o' }, { icon: 'fas fa-pencil' }, { icon: 'fas fa-map-marker' }, { icon: 'fas fa-adjust' }, { icon: 'fas fa-tint' }, { icon: 'fas fa-pencil-square-o' }, { icon: 'fas fa-share-square-o' }, { icon: 'fas fa-check-square-o' }, { icon: 'fas fa-arrows' }, { icon: 'fas fa-step-backward' }, { icon: 'fas fa-fast-backward' }, { icon: 'fas fa-backward' }, { icon: 'fas fa-play' }, { icon: 'fas fa-pause' }, { icon: 'fas fa-stop' }, { icon: 'fas fa-forward' }, { icon: 'fas fa-fast-forward' }, { icon: 'fas fa-step-forward' }, { icon: 'fas fa-eject' }, { icon: 'fas fa-chevron-left' }, { icon: 'fas fa-chevron-right' }, { icon: 'fas fa-plus-circle' }, { icon: 'fas fa-minus-circle' }, { icon: 'fas fa-times-circle' }, { icon: 'fas fa-check-circle' }, { icon: 'fas fa-question-circle' }, { icon: 'fas fa-info-circle' }, { icon: 'fas fa-crosshairs' }, { icon: 'fas fa-times-circle-o' }, { icon: 'fas fa-check-circle-o' }, { icon: 'fas fa-ban' }, { icon: 'fas fa-arrow-left' }, { icon: 'fas fa-arrow-right' }, { icon: 'fas fa-arrow-up' }, { icon: 'fas fa-arrow-down' }, { icon: 'fas fa-share' }, { icon: 'fas fa-expand' }, { icon: 'fas fa-compress' }, { icon: 'fas fa-plus' }, { icon: 'fas fa-minus' }, { icon: 'fas fa-asterisk' }, { icon: 'fas fa-exclamation-circle' }, { icon: 'fas fa-gift' }, { icon: 'fas fa-leaf' }, { icon: 'fas fa-fire' }, { icon: 'fas fa-eye' }, { icon: 'fas fa-eye-slash' }, { icon: 'fas fa-exclamation-triangle' }, { icon: 'fas fa-plane' }, { icon: 'fas fa-calendar' }, { icon: 'fas fa-random' }, { icon: 'fas fa-comment' }, { icon: 'fas fa-magnet' }, { icon: 'fas fa-chevron-up' }, { icon: 'fas fa-chevron-down' }, { icon: 'fas fa-retweet' }, { icon: 'fas fa-shopping-cart' }, { icon: 'fas fa-folder' }, { icon: 'fas fa-folder-open' }, { icon: 'fas fa-arrows-v' }, { icon: 'fas fa-arrows-h' }, { icon: 'fas fa-bar-chart' }, { icon: 'fas fa-twitter-square' }, { icon: 'fas fa-facebook-square' }, { icon: 'fas fa-camera-retro' }, { icon: 'fas fa-key' }, { icon: 'fas fa-cogs' }, { icon: 'fas fa-comments' }, { icon: 'fas fa-thumbs-o-up' }, { icon: 'fas fa-thumbs-o-down' }, { icon: 'fas fa-star-half' }, { icon: 'fas fa-heart-o' }, { icon: 'fas fa-sign-out' }, { icon: 'fas fa-linkedin-square' }, { icon: 'fas fa-thumb-tack' }, { icon: 'fas fa-external-link' }, { icon: 'fas fa-sign-in' }, { icon: 'fas fa-trophy' }, { icon: 'fas fa-github-square' }, { icon: 'fas fa-upload' }, { icon: 'fas fa-lemon-o' }, { icon: 'fas fa-phone' }, { icon: 'fas fa-square-o' }, { icon: 'fas fa-bookmark-o' }, { icon: 'fas fa-phone-square' }, { icon: 'fas fa-twitter' }, { icon: 'fas fa-facebook' }, { icon: 'fas fa-github' }, { icon: 'fas fa-unlock' }, { icon: 'fas fa-credit-card' }, { icon: 'fas fa-rss' }, { icon: 'fas fa-hdd-o' }, { icon: 'fas fa-bullhorn' }, { icon: 'fas fa-bell' }, { icon: 'fas fa-certificate' }, { icon: 'fas fa-hand-o-right' }, { icon: 'fas fa-hand-o-left' }, { icon: 'fas fa-hand-o-up' }, { icon: 'fas fa-hand-o-down' }, { icon: 'fas fa-arrow-circle-left' }, { icon: 'fas fa-arrow-circle-right' }, { icon: 'fas fa-arrow-circle-up' }, { icon: 'fas fa-arrow-circle-down' }, { icon: 'fas fa-globe' }, { icon: 'fas fa-wrench' }, { icon: 'fas fa-tasks' }, { icon: 'fas fa-filter' }, { icon: 'fas fa-briefcase' }, { icon: 'fas fa-arrows-alt' }, { icon: 'fas fa-users' }, { icon: 'fas fa-link' }, { icon: 'fas fa-cloud' }, { icon: 'fas fa-flask' }, { icon: 'fas fa-scissors' }, { icon: 'fas fa-files-o' }, { icon: 'fas fa-paperclip' }, { icon: 'fas fa-floppy-o' }, { icon: 'fas fa-square' }, { icon: 'fas fa-bars' }, { icon: 'fas fa-list-ul' }, { icon: 'fas fa-list-ol' }, { icon: 'fas fa-strikethrough' }, { icon: 'fas fa-underline' }, { icon: 'fas fa-table' }, { icon: 'fas fa-magic' }, { icon: 'fas fa-truck' }, { icon: 'fas fa-pinterest' }, { icon: 'fas fa-pinterest-square' }, { icon: 'fas fa-google-plus-square' }, { icon: 'fas fa-google-plus' }, { icon: 'fas fa-money' }, { icon: 'fas fa-caret-down' }, { icon: 'fas fa-caret-up' }, { icon: 'fas fa-caret-left' }, { icon: 'fas fa-caret-right' }, { icon: 'fas fa-columns' }, { icon: 'fas fa-sort' }, { icon: 'fas fa-sort-desc' }, { icon: 'fas fa-sort-asc' }, { icon: 'fas fa-envelope' }, { icon: 'fas fa-linkedin' }, { icon: 'fas fa-undo' }, { icon: 'fas fa-gavel' }, { icon: 'fas fa-tachometer' }, { icon: 'fas fa-comment-o' }, { icon: 'fas fa-comments-o' }, { icon: 'fas fa-bolt' }, { icon: 'fas fa-sitemap' }, { icon: 'fas fa-umbrella' }, { icon: 'fas fa-clipboard' }, { icon: 'fas fa-lightbulb-o' }, { icon: 'fas fa-exchange' }, { icon: 'fas fa-cloud-download' }, { icon: 'fas fa-cloud-upload-alt' }, { icon: 'fas fa-user-md' }, { icon: 'fas fa-stethoscope' }, { icon: 'fas fa-suitcase' }, { icon: 'fas fa-bell-o' }, { icon: 'fas fa-coffee' }, { icon: 'fas fa-cutlery' }, { icon: 'fas fa-file-text-o' }, { icon: 'fas fa-building-o' }, { icon: 'fas fa-hospital-o' }, { icon: 'fas fa-ambulance' }, { icon: 'fas fa-medkit' }, { icon: 'fas fa-fighter-jet' }, { icon: 'fas fa-beer' }, { icon: 'fas fa-h-square' }, { icon: 'fas fa-plus-square' }, { icon: 'fas fa-angle-double-left' }, { icon: 'fas fa-angle-double-right' }, { icon: 'fas fa-angle-double-up' }, { icon: 'fas fa-angle-double-down' }, { icon: 'fas fa-angle-left' }, { icon: 'fas fa-angle-right' }, { icon: 'fas fa-angle-up' }, { icon: 'fas fa-angle-down' }, { icon: 'fas fa-desktop' }, { icon: 'fas fa-laptop' }, { icon: 'fas fa-tablet' }, { icon: 'fas fa-mobile' }, { icon: 'fas fa-circle-o' }, { icon: 'fas fa-quote-left' }, { icon: 'fas fa-quote-right' }, { icon: 'fas fa-spinner' }, { icon: 'fas fa-circle' }, { icon: 'fas fa-reply' }, { icon: 'fas fa-github-alt' }, { icon: 'fas fa-folder-o' }, { icon: 'fas fa-folder-open-o' }, { icon: 'fas fa-smile-o' }, { icon: 'fas fa-frown-o' }, { icon: 'fas fa-meh-o' }, { icon: 'fas fa-gamepad' }, { icon: 'fas fa-keyboard-o' }, { icon: 'fas fa-flag-o' }, { icon: 'fas fa-flag-checkered' }, { icon: 'fas fa-terminal' }, { icon: 'fas fa-code' }, { icon: 'fas fa-reply-all' }, { icon: 'fas fa-star-half-o' }, { icon: 'fas fa-location-arrow' }, { icon: 'fas fa-crop' }, { icon: 'fas fa-code-fork' }, { icon: 'fas fa-chain-broken' }, { icon: 'fas fa-question' }, { icon: 'fas fa-info' }, { icon: 'fas fa-exclamation' }, { icon: 'fas fa-superscript' }, { icon: 'fas fa-subscript' }, { icon: 'fas fa-eraser' }, { icon: 'fas fa-puzzle-piece' }, { icon: 'fas fa-microphone' }, { icon: 'fas fa-microphone-slash' }, { icon: 'fas fa-shield' }, { icon: 'fas fa-calendar-o' }, { icon: 'fas fa-fire-extinguisher' }, { icon: 'fas fa-rocket' }, { icon: 'fas fa-maxcdn' }, { icon: 'fas fa-chevron-circle-left' }, { icon: 'fas fa-chevron-circle-right' }, { icon: 'fas fa-chevron-circle-up' }, { icon: 'fas fa-chevron-circle-down' }, { icon: 'fas fa-html5' }, { icon: 'fas fa-css3' }, { icon: 'fas fa-anchor' }, { icon: 'fas fa-unlock-alt' }, { icon: 'fas fa-bullseye' }, { icon: 'fas fa-ellipsis-h' }, { icon: 'fas fa-ellipsis-v' }, { icon: 'fas fa-rss-square' }, { icon: 'fas fa-play-circle' }, { icon: 'fas fa-ticket' }, { icon: 'fas fa-minus-square' }, { icon: 'fas fa-minus-square-o' }, { icon: 'fas fa-level-up' }, { icon: 'fas fa-level-down' }, { icon: 'fas fa-check-square' }, { icon: 'fas fa-pencil-square' }, { icon: 'fas fa-external-link-square' }, { icon: 'fas fa-share-square' }, { icon: 'fas fa-compass' }, { icon: 'fas fa-caret-square-o-down' }, { icon: 'fas fa-caret-square-o-up' }, { icon: 'fas fa-caret-square-o-right' }, { icon: 'fas fa-eur' }, { icon: 'fas fa-gbp' }, { icon: 'fas fa-usd' }, { icon: 'fas fa-inr' }, { icon: 'fas fa-jpy' }, { icon: 'fas fa-rub' }, { icon: 'fas fa-krw' }, { icon: 'fas fa-btc' }, { icon: 'fas fa-file' }, { icon: 'fas fa-file-text' }, { icon: 'fas fa-sort-alpha-asc' }, { icon: 'fas fa-sort-alpha-desc' }, { icon: 'fas fa-sort-amount-asc' }, { icon: 'fas fa-sort-amount-desc' }, { icon: 'fas fa-sort-numeric-asc' }, { icon: 'fas fa-sort-numeric-desc' }, { icon: 'fas fa-thumbs-up' }, { icon: 'fas fa-thumbs-down' }, { icon: 'fas fa-youtube-square' }, { icon: 'fas fa-youtube' }, { icon: 'fas fa-xing' }, { icon: 'fas fa-xing-square' }, { icon: 'fas fa-youtube-play' }, { icon: 'fas fa-dropbox' }, { icon: 'fas fa-stack-overflow' }, { icon: 'fas fa-instagram' }, { icon: 'fas fa-flickr' }, { icon: 'fas fa-adn' }, { icon: 'fas fa-bitbucket' }, { icon: 'fas fa-bitbucket-square' }, { icon: 'fas fa-tumblr' }, { icon: 'fas fa-tumblr-square' }, { icon: 'fas fa-long-arrow-down' }, { icon: 'fas fa-long-arrow-up' }, { icon: 'fas fa-long-arrow-left' }, { icon: 'fas fa-long-arrow-right' }, { icon: 'fas fa-apple' }, { icon: 'fas fa-windows' }, { icon: 'fas fa-android' }, { icon: 'fas fa-linux' }, { icon: 'fas fa-dribbble' }, { icon: 'fas fa-skype' }, { icon: 'fas fa-foursquare' }, { icon: 'fas fa-trello' }, { icon: 'fas fa-female' }, { icon: 'fas fa-male' }, { icon: 'fas fa-gratipay' }, { icon: 'fas fa-sun-o' }, { icon: 'fas fa-moon-o' }, { icon: 'fas fa-archive' }, { icon: 'fas fa-bug' }, { icon: 'fas fa-vk' }, { icon: 'fas fa-weibo' }, { icon: 'fas fa-renren' }, { icon: 'fas fa-pagelines' }, { icon: 'fas fa-stack-exchange' }, { icon: 'fas fa-arrow-circle-o-right' }, { icon: 'fas fa-arrow-circle-o-left' }, { icon: 'fas fa-caret-square-o-left' }, { icon: 'fas fa-dot-circle-o' }, { icon: 'fas fa-wheelchair' }, { icon: 'fas fa-vimeo-square' }, { icon: 'fas fa-try' }, { icon: 'fas fa-plus-square-o' }, { icon: 'fas fa-space-shuttle' }, { icon: 'fas fa-slack' }, { icon: 'fas fa-envelope-square' }, { icon: 'fas fa-wordpress' }, { icon: 'fas fa-university' }, { icon: 'fas fa-graduation-cap' }, { icon: 'fas fa-yahoo' }, { icon: 'fas fa-google' }, { icon: 'fas fa-reddit' }, { icon: 'fas fa-reddit-square' }, { icon: 'fas fa-stumbleupon-circle' }, { icon: 'fas fa-stumbleupon' }, { icon: 'fas fa-delicious' }, { icon: 'fas fa-digg' }, { icon: 'fas fa-pied-piper' }, { icon: 'fas fa-pied-piper-alt' }, { icon: 'fas fa-drupal' }, { icon: 'fas fa-joomla' }, { icon: 'fas fa-language' }, { icon: 'fas fa-fax' }, { icon: 'fas fa-building' }, { icon: 'fas fa-child' }, { icon: 'fas fa-paw' }, { icon: 'fas fa-spoon' }, { icon: 'fas fa-cube' }, { icon: 'fas fa-cubes' }, { icon: 'fas fa-behance' }, { icon: 'fas fa-behance-square' }, { icon: 'fas fa-steam' }, { icon: 'fas fa-steam-square' }, { icon: 'fas fa-recycle' }, { icon: 'fas fa-car' }, { icon: 'fas fa-taxi' }, { icon: 'fas fa-tree' }, { icon: 'fas fa-spotify' }, { icon: 'fas fa-deviantart' }, { icon: 'fas fa-soundcloud' }, { icon: 'fas fa-database' }, { icon: 'fas fa-file-pdf-o' }, { icon: 'fas fa-file-word-o' }, { icon: 'fas fa-file-excel-o' }, { icon: 'fas fa-file-powerpoint-o' }, { icon: 'fas fa-file-image-o' }, { icon: 'fas fa-file-archive-o' }, { icon: 'fas fa-file-audio-o' }, { icon: 'fas fa-file-video-o' }, { icon: 'fas fa-file-code-o' }, { icon: 'fas fa-vine' }, { icon: 'fas fa-codepen' }, { icon: 'fas fa-jsfiddle' }, { icon: 'fas fa-life-ring' }, { icon: 'fas fa-circle-o-notch' }, { icon: 'fas fa-rebel' }, { icon: 'fas fa-empire' }, { icon: 'fas fa-git-square' }, { icon: 'fas fa-git' }, { icon: 'fas fa-hacker-news' }, { icon: 'fas fa-tencent-weibo' }, { icon: 'fas fa-qq' }, { icon: 'fas fa-weixin' }, { icon: 'fas fa-paper-plane' }, { icon: 'fas fa-paper-plane-o' }, { icon: 'fas fa-history' }, { icon: 'fas fa-circle-thin' }, { icon: 'fas fa-header' }, { icon: 'fas fa-paragraph' }, { icon: 'fas fa-sliders' }, { icon: 'fas fa-share-alt' }, { icon: 'fas fa-share-alt-square' }, { icon: 'fas fa-bomb' }, { icon: 'fas fa-futbol-o' }, { icon: 'fas fa-tty' }, { icon: 'fas fa-binoculars' }, { icon: 'fas fa-plug' }, { icon: 'fas fa-slideshare' }, { icon: 'fas fa-twitch' }, { icon: 'fas fa-yelp' }, { icon: 'fas fa-newspaper-o' }, { icon: 'fas fa-wifi' }, { icon: 'fas fa-calculator' }, { icon: 'fas fa-paypal' }, { icon: 'fas fa-google-wallet' }, { icon: 'fas fa-cc-visa' }, { icon: 'fas fa-cc-mastercard' }, { icon: 'fas fa-cc-discover' }, { icon: 'fas fa-cc-amex' }, { icon: 'fas fa-cc-paypal' }, { icon: 'fas fa-cc-stripe' }, { icon: 'fas fa-bell-slash' }, { icon: 'fas fa-bell-slash-o' }, { icon: 'fas fa-trash' }, { icon: 'fas fa-copyright' }, { icon: 'fas fa-at' }, { icon: 'fas fa-eyedropper' }, { icon: 'fas fa-paint-brush' }, { icon: 'fas fa-birthday-cake' }, { icon: 'fas fa-area-chart' }, { icon: 'fas fa-pie-chart' }, { icon: 'fas fa-line-chart' }, { icon: 'fas fa-lastfm' }, { icon: 'fas fa-lastfm-square' }, { icon: 'fas fa-toggle-off' }, { icon: 'fas fa-toggle-on' }, { icon: 'fas fa-bicycle' }, { icon: 'fas fa-bus' }, { icon: 'fas fa-ioxhost' }, { icon: 'fas fa-angellist' }, { icon: 'fas fa-cc' }, { icon: 'fas fa-ils' }, { icon: 'fas fa-meanpath' }, { icon: 'fas fa-buysellads' }, { icon: 'fas fa-connectdevelop' }, { icon: 'fas fa-dashcube' }, { icon: 'fas fa-forumbee' }, { icon: 'fas fa-leanpub' }, { icon: 'fas fa-sellsy' }, { icon: 'fas fa-shirtsinbulk' }, { icon: 'fas fa-simplybuilt' }, { icon: 'fas fa-skyatlas' }, { icon: 'fas fa-cart-plus' }, { icon: 'fas fa-cart-arrow-down' }, { icon: 'fas fa-diamond' }, { icon: 'fas fa-ship' }, { icon: 'fas fa-user-secret' }, { icon: 'fas fa-motorcycle' }, { icon: 'fas fa-street-view' }, { icon: 'fas fa-heartbeat' }, { icon: 'fas fa-venus' }, { icon: 'fas fa-mars' }, { icon: 'fas fa-mercury' }, { icon: 'fas fa-transgender' }, { icon: 'fas fa-transgender-alt' }, { icon: 'fas fa-venus-double' }, { icon: 'fas fa-mars-double' }, { icon: 'fas fa-venus-mars' }, { icon: 'fas fa-mars-stroke' }, { icon: 'fas fa-mars-stroke-v' }, { icon: 'fas fa-mars-stroke-h' }, { icon: 'fas fa-neuter' }, { icon: 'fas fa-facebook-official' }, { icon: 'fas fa-pinterest-p' }, { icon: 'fas fa-whatsapp' }, { icon: 'fas fa-server' }, { icon: 'fas fa-user-plus' }, { icon: 'fas fa-user-times' }, { icon: 'fas fa-bed' }, { icon: 'fas fa-viacoin' }, { icon: 'fas fa-train' }, { icon: 'fas fa-subway' }, { icon: 'fas fa-medium' }];

        var itemTemplate = $('.icon-picker-list').clone(true).html();

        $('.icon-picker-list').html('');

        // Loop through JSON and appends content to show icons
        $(icons).each(function(index) {
            var itemtemp = itemTemplate;
            var item = icons[index].icon;

            if (index == selectedIcon) {
                var activeState = 'active'
            } else {
                var activeState = ''
            }

            itemtemp = itemtemp.replace(/#item#/g, item).replace(/#index#/g, index).replace(/#activeState#/g, activeState);

            $('.icon-picker-list').append(itemtemp);

        });

        // Variable that's passed around for active states of icons
        var selectedIcon = null;

        $('.icon-class-input').each(function() {
            if ($(this).val() != null) {
                $(this).siblings('.demo-icon').addClass($(this).val());
            }
        });

        // To be set to which input needs updating
        var iconInput = null;

        // Click function to set which input is being used
        $('.picker-button').click(function() {

            $('#iconPicker').modal('show');

        });

        // Click function to select icon
        $(document).on('click', '.icon-picker-list a', function() {
            // Sets selected icon
            selectedIcon = $(this).data('index');

            // Removes any previous active class
            $('.icon-picker-list a').removeClass('active');
            // Sets active class
            $('.icon-picker-list a').eq(selectedIcon).addClass('active');
        });

        // Update icon input
        $('#change-icon').click(function() {
            $('#iconPicker').modal('hide');
        });

        function findInObject(object, property, value) {
            for (var i = 0; i < object.length; i += 1) {
                if (object[i][property] === value) {
                    return i;
                }
            }
        }

        $('.picker-button').click(function() {

            $('#iconPicker').modal('show');

        });
        var menuicon=$('#mod-menuleft1 ul li a span');
        var element='';
        menuicon.click(function () {
            var el1=$(this);
            var iconel=$('.icon-picker-list li');
            iconel.click(function(){
                var optionid=el1.attr('data-id');
                var icon=$.trim($(this).text());
                var iconupdated=icon.replace("fas fa-","");
                var classe="<span class='icon "+icon+"'></span>";
                el1.html(classe);
                $.ajax({
                    type: 'POST',
                    url: $.service('menu', 'edit_icon'),
                    data: {updatedicon:iconupdated,optionid:optionid},
                    dataType: 'text',
                    success: function(data) {
                        //msg
                    },
                    error: function ( jqXHR, textStatus, errorThrown ) {
                        //error
                    }
                });
            });
        });

        /*end icon picker script*/

    </script>
{/literal}