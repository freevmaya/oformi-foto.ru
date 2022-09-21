<?                  
    header('Content-Type: text/html; charset=windows-1251');
    header('Cache-Control: no-store, no-cache, must-revalidate');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru" dir="ltr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=windows-1251" />
    <script type="text/javascript">
                    
        function init() {
            var hashA = document.location.hash.substr(1).split('&');
            var params = {
                application_key: 'CBAODNHIABABABABA'
            };
            for (var i=0; i<hashA.length; i++) {
                var p = hashA[i].split('='); 
                eval("params['" + p[0] + "'] = '" + p[1] + "'"); 
            }
            
            /*                        
            
            OkClient.initialize('http://api.odnoklassniki.ru/', params);
            
            var callback_users_getCurrentUser = function(method, result, data){
                if (result) {
                    fillCard(result);
                } else {
                    processError(data);
                }
            };            
            OkClient.call({"method":"users.getCurrentUser"}, callback_users_getCurrentUser);;
            */
            
            window.opener.authSuccess(params);                                    
            window.close();                                                
        }
    </script>    
<body onload="init()">    
</body>
</html>