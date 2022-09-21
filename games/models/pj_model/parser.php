<?
//185%7E1%2C0%2C0%2C1%2C-88.56451612903226%2C-13.112903225806463%7Econtent.foto.my.mail.ru%2Fmail%2Ffwadim%2F_myphoto%2Fi-9.jpg%7E0
function cardInfoParse($cardInfo) {
    $result = new stdClass();
    $arr = explode('~', $cardInfo);
    $result->cardID = $arr[0];
    $result->transform  = $arr[1];
    $result->imageURL   = 'http://'.$arr[2];
    $result->pathinfo   = pathinfo($arr[2]);
    $result->pathexp    = explode('/', $result->pathinfo['dirname']);
    $result->userEmail  = $result->pathexp[2].'@mail.ru';
    $result->userURL    = 'http://my.mail.ru/mail/'.$result->pathexp[2];   
    return $result;
}

function getCardURL($cardID) {
    $fileName = 'i'.$cardID.'.png';
    if (file_exists(CARDS_PATH.'preview'.DS.$fileName)) return CARDS_URL.'preview'.DS.$fileName;
    else return '';
}
?>