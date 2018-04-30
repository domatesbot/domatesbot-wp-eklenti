<!doctype html>
<html>
    <head>
        <title>DOMATESBOT Adult Video</title> 
        <meta name="ROBOTS" content="NOINDEX, NOFOLLOW"> 
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta name="referrer" content="none">
        <style type="text/css">  html,body{margin:0;padding:0;height:100% !important;background-color:#000000;overflow:hidden}</style>   
        <script type="text/javascript" src="https://content.jwplatform.com/libraries/W23N1P02.js"></script>
        <script src="http://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="domatesVideo"> </div>
        <script>
            var getUrlParameter = function getUrlParameter(sParam) {
                var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                    sURLVariables = sPageURL.split('&'),
                    sParameterName,
                    i;
                
                    for (i = 0; i < sURLVariables.length; i++) {
                        sParameterName = sURLVariables[i].split('=');
                
                        if (sParameterName[0] === sParam) {
                            return sParameterName[1] === undefined ? true : sParameterName[1];
                        }
                    }
            };
            var apiURL =  atob(getUrlParameter('h'));
            var videoID = getUrlParameter('v');
            var imageURL = atob(getUrlParameter('i'));
            var ref =location.hostname;
            if(document.referrer.indexOf(location.hostname) !== -1 || !document.referrer){
                var x = document.createElement("script");
                x.src = apiURL + "/player.js";
                document.getElementsByTagName("head")[0].appendChild(x),
                x.onload = function() {
                    console.log("HAZIRIZ KAPTAN!");
                };
            }else {
                jQuery("#domatesVideo").html("YASAK!!!");
                console.log("dedeler");
            }
        </script>
    </body>
</html>
