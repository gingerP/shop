<?php
include_once __DIR__.'/../../../back/import/import.php';
include_once __DIR__.'/../../../back/import/tags.php';

class SearchEngines {

    public static function getGoogleAnalyticScript() {
        $script = new Script();
        $script->addChild("
              (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

              ga('create', 'UA-60005077-1', 'auto');
              ga('send', 'pageview');
        ");
        return $script;
    }

    public static function getYandexMetricScript() {
        return "<!-- Yandex.Metrika counter -->
            <script type=\"text/javascript\">
                (function (d, w, c) {
                    (w[c] = w[c] || []).push(function() {
                        try {
                            w.yaCounter37927730 = new Ya.Metrika({
                                id:37927730,
                                clickmap:true,
                                trackLinks:true,
                                accurateTrackBounce:true,
                                webvisor:true
                            });
                        } catch(e) { }
                    });

                    var n = d.getElementsByTagName(\"script\")[0],
                        s = d.createElement(\"script\"),
                        f = function () { n.parentNode.insertBefore(s, n); };
                    s.type = \"text/javascript\";
                    s.async = true;
                    s.src = \"https://mc.yandex.ru/metrika/watch.js\";

                    if (w.opera == \"[object Opera]\") {
                        d.addEventListener(\"DOMContentLoaded\", f, false);
                    } else { f(); }
                })(document, window, \"yandex_metrika_callbacks\");
            </script>
            <noscript><div><img src=\"https://mc.yandex.ru/watch/37927730\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>
            <!-- /Yandex.Metrika counter -->";
    }

} 