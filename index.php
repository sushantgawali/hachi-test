<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.css">
</head>
<body>
<div class="container">
<?php

require_once 'lib/google/src/Google_Client.php';
require_once 'lib/google/src/contrib/Google_Oauth2Service.php';
session_start();

$client = new Google_Client();
$client->setScopes("http://www.google.com/m8/feeds/");

$google_oauthV2 = new Google_Oauth2Service($client);

if (isset($_GET['code'])) {
    $client->authenticate();
    $_SESSION['token'] = $client->getAccessToken();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['token']);
    $client->revokeToken();
}

if ($client->getAccessToken()) {

    $req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full");
    $val = $client->getIo()->authenticatedRequest($req);
    $response = simplexml_load_string($val->getResponseBody());

    ?>

<h4>Contacts</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email Id</th>
            <th>Phone Number</th>
        </tr>
    </thead>
    <?php foreach($response->entry as $entry){ ?>
    <tbody>
        <tr>
            <td><?php
                if($entry->title != 'null'){
                    echo $entry->title;
                }else{
                    echo 'N.A.';
                }
                ?></td>
            <td><?php $email = $entry->xpath('gd:email');
                echo $email[0]->attributes()->address ;?></td>
            <td>
                <?php
                $number = $entry->xpath('gd:phoneNumber');
                if($number){
                    echo $number[0];
                }else{
                    echo 'N.A.';
                }?>
            </td>
        </tr>
    </tbody>
    <?php } ?>
</table>

<?php
    $_SESSION['token'] = $client->getAccessToken();
} else {
    $auth = $client->createAuthUrl();
}

if (isset($auth)) {
    print "<a class='login btn btn-primary' href='$auth'>Login With Gmail</a>";
} else {
   
    print "<a class='logout btn btn-primary text-center' href='?logout'>Logout</a>";
}
?>
</div>

</body>
</html>
