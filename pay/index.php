<?php
require_once('../assets/includes/core.php');
include 'header.php';
$configData = configItem();
if(isset($_GET['package'])){
    $package = secureEncode($_GET['package']);
} else {
    $package = 0;
}

if(isset($_GET['type'])){
    $type = secureEncode($_GET['type']);
    if($type == 'credits'){
        $packageArray = $sm['creditsPackages'];
        $item_name = $packageArray[$package]['credits'].' '.$sm['lang'][73]['text'];
    } else {
        $packageArray = $sm['premiumPackages'];
        $item_name = $packageArray[$package]['days'].' '.$sm['lang'][332]['text'];
    }
} else {
    header('Location:'.$sm['config']['site_url']);
    exit;
}

if($logged === false){
    header('Location:'.$sm['config']['site_url']);
    exit;
}

if(isset($_GET['fromMobile'])){
    $mobileUrl = 'mobile/';
} else {
    $mobileUrl = '';
}

$INR_prices = array($sm['plugins']['instamojo']['conversion'],$sm['plugins']['paytm']['conversion'],$sm['plugins']['payu']['conversion'],$sm['plugins']['razorpay']['conversion']);

$EUR_price = $packageArray[$package]['price'];
$USD_price = $packageArray[$package]['price'];
$INR_price = ceil($packageArray[$package]['price'] * max($INR_prices));
$TRY_price = ceil($packageArray[$package]['price'] * $sm['plugins']['iyzico']['conversion']);
$NGN_price = ceil($packageArray[$package]['price'] * $sm['plugins']['paystack']['conversion']);

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $sm['config']['name']; ?> | <?= $sm['lang'][868]['text']; ?> </title>
      <script src="assets/js/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/custom.css" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Rubik" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="../themes/default/css/crossplatform.css"/>
    <link href="<?php echo $sm['config']['theme_url']; ?>/css/vendor/jquery.dm-uploader.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo $sm['config']['theme_url']; ?>/css/vendor/sweetalert.css">
    <style type="text/css">
        :root {
          touch-action: pan-x pan-y;
          height: 100%
        }

        ::-webkit-scrollbar {
          width: 6px;
          height: 6px;
        }
        /* Track */
        ::-webkit-scrollbar-track {
          border-radius: 100vh;
          background: none;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
          background: rgba(0,0,0,.2);
          border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
          background: rgba(0,0,0,.4);
        }

        body{
            font-family: 'Rubik' !important;
        }
        .form-control{
            background: #101010;
            border:none;
            border-radius: 5px;
        }
        .loader{
            back
        }
        .method {
          letter-spacing: 0.75px;
          color: #c1c8d0;
          padding: 0 165px;
          position: relative;
          list-style: none;
          color: #fafafa !important;
          cursor: pointer;
          margin-bottom: 15px;
          border-radius: 15px;
          background: #121212;
          border:2px solid #121212;
          height: 55px;
        }
        .method .cards-icons{
          position: absolute;
          width: auto;
          height: 45px;
          right: calc(10px / 2);
          top: 30%;

        }
        .method a{
            color: #fafafa;
            font-weight: bold;
        }
        .method:last-of-type {
        }
        .method .gateway-icon {
          position: absolute;
          width: auto;
          height: 45px;
          left: calc(115px / 2);
          top: 50%;
          transform: translate(-40%, -50%);
        }
        .method.active {
          border:2px solid #0abd07;
        }
        .method.active a{

        }
        .method > a {
          line-height: 55px;
        }
        .list-group-item{
            border:none !important;
            margin-bottom: 15px;
        }
        .method{
            padding: 0 30px;
        }
        .method .gateway-icon {
            display: none;
        }
        @media only screen and (max-width: 680px) {
            .method{
                padding: 0 20px;
            }
            .method .gateway-icon {
                display: none;
            }
            .method .cards-icons{
                height:34px;
                top: 9px;
            }
        }
        .user-icon {
            height: 70px;
            width: 70px;
            display: inline-block;
            background-size:   cover;
            background-repeat: no-repeat;
            background-position: top center;
            border-radius: 50%;
            vertical-align:middle;
            margin-left: 5px;
        }
        .card-header{
            background-image: url('assets/img/bg-header.jpg');
            background-size: cover;
        }
    </style>
    <script> var checkoutType = '<?= $type; ?>'; </script>
</head>
<body style="background: #0a0a0a">

    <div class="d-flex justify-content-center" id="loader">
        <div class="lw-page-loader" style="background:#0a0a0a">
            <div class="spinner-border"  role="status"></div>
        </div>
    </div>

    <div class="mb-5 container" style="max-width: 400px" id="lwCheckoutForm">
        <div class="row">
            <div class="col-md-12" style="padding:0">
                <form method="post" id="lwPaymentForm">
                    <div class="card" style="border:none;background: none;">
                        <div class="card-body" style="padding:5px">
                            <div class="alert alert-info" style="display: none"></div>
                            <div id="lwValidationMessage" class="lw-validation-message"></div>
                            <?php
                                //$configData = configItem();
                                $userDetails = [
                                    'amounts' => [
                                        'USD'   => $USD_price,
                                        'INR'   => $INR_price,
                                        'NGN'   => $NGN_price,
                                        'TRY'   => $TRY_price,
                                        'EUR'   => $EUR_price
                                    ],
                                    'order_id'      => uniqid(),
                                    'order_type'      => $type,
                                    'order_package'      => $package,
                                    'customer_id'   => $sm['user']['id'],
                                    'item_name'     => $item_name,
                                    'item_qty'      => 1,
                                    'item_id'       => 'ITEM' . uniqid(), // required in Iyzico, Paytm gateways
                                    'payer_email'   => $sm['user']['email'], // required in instamojo, Iyzico, Stripe gateways
                                    'payer_name'    => $sm['user']['name'], // required in instamojo, Iyzico gateways
                                    'payer_mobile'  => '9999999999',
                                    'description'   => $item_name.' - '.$sm['config']['name'],
                                    'ip_address'    => $sm['user']['ip'],
                                    'address'       => '3234 Aligar Street Bali',
                                    'city'          => 'Cali',  // required in Iyzico gateways
                                    'country'       => $sm['user']['country'] // required in Iyzico gateways
                                ];
                                if(!in_array($sm['plugins']['settings']['currency'], $userDetails['amounts'])){
                                    $userDetails['amounts'][$sm['plugins']['settings']['currency']] = $USD_price;
                                }

                                if (!$configData) {
                                    echo '<div class="alert alert-warning text-center">Unable to load configuration.</div>';
                                } else {
                                    $configItem = $configData['payments']['gateway_configuration'];
                                ?>

                                <h5 class="mt-3 text-center" style="color:#fafafa;font-size: 0.9rem"><?= $sm['lang'][869]['text']; ?></h5>
                                <div class="card-content">
                                    <ul class="card-content--methods" style="padding-left: 0">
                                        <A HREF="https://bill.ccbill.com/jpost/signup.cgi?clientAccnum=953926&clientSubacc=0004&formName=141cc&language=English&allowedTypes=0000003226:840&subscriptionTypeId=0000003226:840" >
                                            Join Now</A>
                                        <?php
                                    // Show all enable payment Gateway
                                    foreach ($configItem as $key => $value) {
                                        // Check if payment gateway is enable from config
                                        if ($value['enable']) { ?>
                                            <?php
                                            $selected = '';
                                            if(isset($_GET['gateway'])){
                                                if($value['gateway'] == $_GET['gateway']){
                                                    $selected = 'active';
                                                }
                                            }
                                            ?>
                                            <?php if($key == 'fortumo' && $type == 'premium'){ continue; } ?>
                                            <input type="radio" style="display: none" value="<?= $key ?>" name="paymentOption" data-gateway="<?= $value['gateway']; ?>">
                                            <li class="method box-shadow method--<?= $key; ?> <?= $selected; ?>"  data-gateway-key="<?= $key; ?>" data-custom-payment="0">
                                                <a href="javascript:;" style="text-decoration: none"><?= $value['gateway']; ?></a>
                                                <?php if($key != 'fortumo') { ?>
                                                <img class="cards-icons" src="assets/img/<?= $key ?>-big.png">
                                                <?php } ?>
                                            </li>
                                        <?php
                                        }
                                    }

                                    $cp = getArray('custom_payments','where p_status = 1','id asc');
                                    if(!empty($cp)){
                                        foreach ($cp as $p) {
                                            if(!empty($p['p_external_url'])){
                                                $p_external_url = $p['p_external_url'];
                                            } else {
                                                $p_external_url = 'No';
                                            }
                                            if(!empty($p['p_logo'])){
                                                $p_logo = $p['p_logo'];
                                            } else {
                                                $p_logo = 'No';
                                            }
                                            if(!empty($p['p_country'])){
                                                if($sm['user']['country_code'] != $p['p_country']){
                                                    continue;
                                                }
                                            }
                                            if($p['p_type'] == 2 && $type != 'credits'){
                                                continue;
                                            }
                                            if($p['p_type'] == 3 && $type != 'premium'){
                                                continue;
                                            }
                                            if($p['p_package'] != 9 && $p['p_package'] != $package){
                                                continue;
                                            }

                                            $checkFilter = 'WHERE order_gateway = "'.$p['p_name'].'" AND order_type = "'.$type.'" AND order_title = "'.$item_name.'" AND order_status = "pending"';
                                            $checkPending = getData('orders','order_id',$checkFilter);
                                            if($checkPending != 'noData'){
                                                $pendingReview = 'Yes';
                                            } else {
                                                $pendingReview = 'No';
                                            }
                                        ?>
                                        <input type="radio" style="display: none" value="<?= $p['p_name'] ?>" name="paymentOption" data-gateway="<?= $p['p_name'] ?>" >
                                        <li class="method box-shadow method--<?= $p['p_name'] ?> <?= $selected; ?>" data-custom-payment="1" data-gateway-key="<?= $p['p_name'] ?>" data-gateway-desc="<?= $p['p_description'] ?>"
                                        data-gateway-proof="<?= $p['p_proof'] ?>"
                                        data-gateway-review-time="<?= $p['p_review_time'] ?>" data-gateway-external-url="<?= $p_external_url;?>"
                                        data-gateway-logo="<?= $p_logo;?>"
                                        data-gateway-pending="<?=$pendingReview; ?>"
                                        data-gateway-order="<?=$checkPending ; ?>">
                                        <a href="javascript:;" style="text-decoration: none"><?= $p['p_name'] ?></a>
                                            <?php if(!empty($p['p_logo'])){ ?>
                                                <img class="cards-icons" src="<?=$p['p_logo']; ?>">
                                            <?php } ?>
                                        </li>
                                       <?php }
                                    }
                                    ?>
                                    </ul>
                                </div>

                                <div class="lw-iyzico-form card mb-3" id="cardCheckoutForm">
                                    <div class="card-header">
                                        <h5 class="card-title display-td" style="color: #fff"><?= $sm['lang'][870]['text']; ?></h5>
                                        <small class="text-danger"><?= $sm['lang'][871]['text']; ?></small>
                                    </div>

                                    <div class="card-body mb-3">

                                        <div class="form-group">
                                            <label for="cname"><?= $sm['lang'][872]['text']; ?></label>
                                            <input type="text" class="form-control" id="cname" name="cardname" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label for="cardNumber"><?= $sm['lang'][873]['text']; ?></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="cardNumber" name="cardnumber">
                                                <div class="input-group-append" id="basic-addon1">
                                                    <span class="input-group-text"><i class="fa fa-credit-card"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="col">
                                                <label for="expmonth"><?= $sm['lang'][874]['text']; ?></label>
                                                <input type="number" class="form-control" id="expmonth" name="expmonth">
                                            </div>
                                            <div class="col">
                                                <label for="expyear"><?= $sm['lang'][875]['text']; ?></label>
                                                <input type="number" class="form-control" id="expyear" name="expyear">
                                            </div>
                                            <div class="col">
                                                <label for="cvv">CVV</label>
                                                <input type="number" class="form-control" id="cvv" name="cvv">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="text-center" style="color: #28a745;font-size: 11px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                 <?= $sm['lang'][876]['text']; ?></h5>

                                <button type="submit" id="button-payment" value="<?= $sm['lang'][877]['text']; ?>" class="btn btn-block btn-success box-shadow" style="font-weight: bold;border-radius: 10px;display: none"><?= $sm['lang'][877]['text']; ?></button>

                            </div><?php } ?>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>


    <div class="modal fade" id="customPaymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content" style="border:none;">
          <div class="modal-body" style="padding:0">
            <div class="card" id="customPayment" style="display: none;border:none;background:#0a0a0a">
                <div class="card-body mb-3" data-proof-form="sent" style="display:none">
                    <div class="customPaymentLogo" style="display:none">
                        <center>
                            <img class="customPaymentLogoImg" src="" style="max-height: 45px;margin-bottom:15px;display: inline-table;">
                        </center>
                    </div>
                    <h3 class="text-center" style="margin-top:25px"><?= $sm['lang'][1018]['text']; ?></h3>
                    <p class="text-center" style="margin-top:40px">
                        <?= $sm['lang'][1019]['text']; ?> <strong class="customPaymentReviewTime"></strong>
                    </p>
                    <div class="" style="position:relative;padding: 15px;background: #fff;border-radius: 5px;font-size: 16px;margin-bottom: 15px;padding-top: 20px;">
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between  " style="background: #f7d6368c;border: 1px solid #F7D636!important">
                              <div>
                                <span class="my-0"><?= $item_name;?></span>
                              </div>
                              <span><?= $sm['lang'][1017]['text']; ?> <strong id="paymentOrderId"></strong></span>
                            </li>
                        </ul>
                    </div>
                    <p class="text-center" style="margin-top:20px">
                        <?= $sm['lang'][1029]['text']; ?> <strong><?= $sm['config']['email']; ?></strong> <?= $sm['lang'][1020]['text']; ?>
                    </p>
                </div>
                <div class="card-body mb-3" data-proof-form="send">
                    <small style="margin: 10px;text-align: center;">
                        <a href="javascript:;" onclick="closeCustomPaymentModal()" style="color:#fafafa">
                            <?= $sm['lang'][1030]['text']; ?>
                        </a>
                    </small>
                    <div class="customPaymentLogo" style="display:none">
                        <center>
                            <img class="customPaymentLogoImg" src="" style="max-height: 65px;margin-bottom:15px;display: inline-table;">
                        </center>
                    </div>
                    <p class="text-center" style="color: #fafafa;">
                        <?= $sm['lang'][1021]['text']; ?>
                    </p>
                    <div class="" style="position:relative;padding: 15px;background: #101010;border-radius: 5px;font-size: 16px;margin-bottom: 15px;padding-top: 20px;">
                        <div style="position:relative;width: 100%;">

                        <p class="text-center" style="color:#fafafa" data-custom-payment-external-url>
                            <?= $sm['lang'][1022]['text']; ?>
                        </p>

                        <a data-custom-payment-external-url id="button-custom-payment-external-url" target="_blank" class="btn btn-lg btn-block btn-info box-shadow"><?= $sm['lang'][1023]['text']; ?> <?=$USD_price; ?> <?= $sm['plugins']['settings']['currency']; ?></a>

                        <p class="text-center" data-custom-payment-desc id="customPaymentInstructions"
                        style="color:#fafafa;font-weight: 500;font-size: 16px;"></p>
                        </div>
                    </div>
                    <p class="text-center" style="margin-bottom:20px;padding-right:10px;padding-left:10px;color: #999;font-size: 0.8rem;">
                        <?= $sm['lang'][1024]['text']; ?><br>
                        <?= $sm['lang'][1025]['text']; ?> <strong class="customPaymentReviewTime"></strong></p>
                    <form action="" id="submitCustomPayment">
                        <div class="form-group">
                            <label class="text-center" style="color: #fafafa;">
                                <?= $sm['lang'][1026]['text']; ?>
                            </label>
                            <input class="form-control" name="proof" required type="text" id="customPaymentProof" placeholder=""/>
                        </div>

                        <div class="form-group">
                            <label class="text-center" style="color: #fafafa;">
                                <?= $sm['lang'][1027]['text']; ?>
                            </label>
                            <div class="input-group" id="upload-proof">
                                <input type="file" id="proof_image_input" class="form-control">
                                <div class="input-group-append" style="background: #101010;">
                                    <span class="input-group-text"><i class="fa fa-file-image-o"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="progress" id="proof_image_progress" style="margin-top:10px;display: none;">
                              <div class="progress-bar" id="proof_image_progress_bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <input type="hidden" name="action" value="sendPaymentProof">
                        <input type="hidden" name="gateway" id="customPaymentNameGateway">
                        <input type="hidden" name="package" value="<?= $package; ?>">
                        <input type="hidden" name="price" value="<?=$USD_price; ?>">
                        <input type="hidden" name="title" value="<?= $item_name;?>">
                        <input type="hidden" name="type" value="<?= $type;?>">
                        <input type="hidden" name="user" value="<?= $sm['user']['id'];?>"> <input type="hidden" id="proof_image" name="proof_image">

                        <button type="submit" id="button-custom-payment" class="btn btn-block btn-success box-shadow"><?= $sm['lang'][1028]['text']; ?></button>
                    </form>
                </div>
                <small style="margin: 10px;text-align: center;">
                    <a href="javascript:;" onclick="closeCustomPaymentModal()">
                        <?= $sm['lang'][1030]['text']; ?>
                    </a>
                </small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CCBILL -->
    <div class="modal fade" id="ccBillPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border:none;">
                <div class="modal-body" style="padding:0">
                    <div class="card" id="customPayment" style="display: none;border:none;background:#0a0a0a">
                        <div class="card-body mb-3" data-proof-form="sent" style="display:none">
                            <h3 class="text-center" style="margin-top:25px"></h3>
                            <p class="text-center" style="margin-top:40px">
                                 <strong class="customPaymentReviewTime"></strong>
                            </p>
                            <div class="" style="position:relative;padding: 15px;background: #fff;border-radius: 5px;font-size: 16px;margin-bottom: 15px;padding-top: 20px;">
                                <ul class="list-group mb-3">
                                    <li class="list-group-item d-flex justify-content-between  " style="background: #f7d6368c;border: 1px solid #F7D636!important">
                                        <div>

                                        </div>
                                        <span><strong id="paymentOrderId"></strong></span>
                                    </li>
                                </ul>
                            </div>
                            <p class="text-center" style="margin-top:20px">

                            </p>
                        </div>
                        <div class="card-body mb-3" data-proof-form="send">
                            <small style="margin: 10px;text-align: center;">
                                <a href="javascript:;" onclick="closeCustomPaymentModal()" style="color:#fafafa">
                                    <?= $sm['lang'][1030]['text']; ?>
                                </a>
                            </small>
                            <div class="customPaymentLogo" style="display:none">
                                <center>
                                    <img class="customPaymentLogoImg" src="" style="max-height: 65px;margin-bottom:15px;display: inline-table;">
                                </center>
                            </div>
                            <p class="text-center" style="color: #fafafa;">
                                <?= $sm['lang'][1021]['text']; ?>
                            </p>
                            <div class="" style="position:relative;padding: 15px;background: #101010;border-radius: 5px;font-size: 16px;margin-bottom: 15px;padding-top: 20px;">
                                <div style="position:relative;width: 100%;">

                                    <p class="text-center" style="color:#fafafa" data-custom-payment-external-url>
                                        <?= $sm['lang'][1022]['text']; ?>
                                    </p>

                                    <a data-custom-payment-external-url id="button-custom-payment-external-url" target="_blank" class="btn btn-lg btn-block btn-info box-shadow"><?= $sm['lang'][1023]['text']; ?> <?=$USD_price; ?> <?= $sm['plugins']['settings']['currency']; ?></a>

                                    <p class="text-center" data-custom-payment-desc id="customPaymentInstructions"
                                       style="color:#fafafa;font-weight: 500;font-size: 16px;"></p>
                                </div>
                            </div>
                            <p class="text-center" style="margin-bottom:20px;padding-right:10px;padding-left:10px;color: #999;font-size: 0.8rem;">
                                <?= $sm['lang'][1024]['text']; ?><br>
                                <?= $sm['lang'][1025]['text']; ?> <strong class="customPaymentReviewTime"></strong></p>
                            <form action="" id="submitCustomPayment">
                                <div class="form-group">
                                    <label class="text-center" style="color: #fafafa;">
                                        <?= $sm['lang'][1026]['text']; ?>
                                    </label>v
                                    <input class="form-control" name="proof" required type="text" id="customPaymentProof" placeholder=""/>
                                </div>

                                <div class="form-group">
                                    <label class="text-center" style="color: #fafafa;">
                                        <?= $sm['lang'][1027]['text']; ?>
                                    </label>
                                    <div class="input-group" id="upload-proof">
                                        <input type="file" id="proof_image_input" class="form-control">
                                        <div class="input-group-append" style="background: #101010;">
                                    <span class="input-group-text"><i class="fa fa-file-image-o"></i>
                                    </span>
                                        </div>
                                    </div>
                                    <div class="progress" id="proof_image_progress" style="margin-top:10px;display: none;">
                                        <div class="progress-bar" id="proof_image_progress_bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <input type="hidden" name="action" value="sendPaymentProof">
                                <input type="hidden" name="gateway" id="customPaymentNameGateway">
                                <input type="hidden" name="package" value="<?= $package; ?>">
                                <input type="hidden" name="price" value="<?=$USD_price; ?>">
                                <input type="hidden" name="title" value="<?= $item_name;?>">
                                <input type="hidden" name="type" value="<?= $type;?>">
                                <input type="hidden" name="user" value="<?= $sm['user']['id'];?>"> <input type="hidden" id="proof_image" name="proof_image">

                                <button type="submit" id="button-custom-payment" class="btn btn-block btn-success box-shadow"><?= $sm['lang'][1028]['text']; ?></button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer text-center">
        <small class="text-muted" style="font-size: 11px"><?= $sm['lang'][878]['text']; ?> <a href="<?= $sm['config']['site_url']; ?>"><?= $sm['config']['name']; ?></a> <?= $sm['lang'][879]['text']; ?>.<br><?= $sm['config']['name']; ?> <?= $sm['lang'][880]['text']; ?>.
    </footer>
    <?php
    if($type == 'credits'){
        $paypalItemName = $sm['config']['name'].' - '.$packageArray[$package]['credits'].' '.$sm['lang'][73]['text'];
        $paypalItemCustom = $sm['user']['id'].','.$packageArray[$package]['credits'];
    } else {
        $paypalItemName = $sm['config']['name'].' - '.$packageArray[$package]['days'].' '.$sm['lang'][332]['text'];
        $paypalItemCustom = $sm['user']['id'].','.$packageArray[$package]['days'];
    }

    ?>

    <?php if($type == 'credits'){ ?>
        <form id="paypalForm" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?= $sm['plugins']['paypal']['email']; ?>">
            <input type="hidden" name="item_name" value="<?= $paypalItemName; ?>">
            <input type="hidden" name="currency_code" value="<?= $sm['plugins']['settings']['currency']; ?>">
            <input type="hidden" name="amount" value="<?= $packageArray[$package]['price']; ?>">
            <input type="hidden" name="custom" value="<?=$paypalItemCustom; ?>">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="notify_url" value="<?= $sm['config']['site_url']; ?>assets/sources/ipn.php">
            <input type="hidden" name="return" value="<?= $sm['config']['site_url']; ?>credits-ok">
        </form>
    <?php } else { ?>
        <form id="paypalSubscribe" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_xclick-subscriptions">
            <input type="hidden" name="business" value="<?= $sm['plugins']['paypal']['email']; ?>">
            <input type="hidden" name="item_name" value="<?= $paypalItemName; ?>">
            <input type="hidden" name="currency_code" value="<?= $sm['plugins']['settings']['currency']; ?>">
            <input type="hidden" name="a3" value="<?= $packageArray[$package]['price']; ?>">
            <input type="hidden" name="p3" value="<?= $packageArray[$package]['days']; ?>">
            <input type="hidden" name="t3" value="D">
            <input type="hidden" name="src" value="1">
            <input type="hidden" name="sra" value="1">
            <input type="hidden" name="custom" value="<?=$paypalItemCustom; ?>">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="notify_url" value="<?= $sm['config']['site_url']; ?>assets/sources/ipnpremium.php">
            <input type="hidden" name="return" value="<?= $sm['config']['site_url']; ?>">
        </form>
    <?php } ?>



<script src="<?php echo $sm['config']['theme_url']; ?>/js/vendor/sweetalert.min.js"></script>
<script src="<?php echo $sm['config']['theme_url']; ?>/js/vendor/jquery.dm-uploader.min.js"></script>
</body>

</html>
<?php
    // Get config data
    $config             = getPublicConfigItem();
    // Get app URL
    $paymentPagePath    = getAppUrl();

    $gatewayConfiguration = $config['payments']['gateway_configuration'];

    // get paystack config data
    $paystackConfigData = getArrayItem($gatewayConfiguration, 'paystack', []);
    // Get paystack callback ur
    $paystackCallbackUrl= getAppUrl(getArrayItem($paystackConfigData, 'callbackUrl', ''));

    // Get stripe config data
    $stripeConfigData   = getArrayItem($gatewayConfiguration, 'stripe', []);
    // Get stripe callback ur
    $stripeCallbackUrl  = getAppUrl(getArrayItem($stripeConfigData, 'callbackUrl', ''));

    // Get razorpay config data
    $razorpayConfigData = getArrayItem($gatewayConfiguration, 'razorpay', []);
    // Get razorpay callback url
    $razorpayCallbackUrl= getAppUrl(getArrayItem($razorpayConfigData, 'callbackUrl'));

    // Get Authorize.Net config Data
    $authorizeNetConfigData = getArrayItem($gatewayConfiguration, 'authorize-net', []);
    // Get Authorize.Net callback url
    $authorizeNetCallbackUrl= getAppUrl(getArrayItem($authorizeNetConfigData, 'callbackUrl'));

    // Individual payment gateway url
    $individualPaymentGatewayAppUrl = getAppUrl('individual-payment-gateways');

    // Get payUmoney config data
    $payUmoneyConfigData = getArrayItem($gatewayConfiguration, 'payumoney', []);
?>

<script type="text/javascript">
    var paymentGateway = '';

    $(window).on( "load", function(){
        setTimeout(function(){
            $('#loader').remove();
        },125);
    });

    function closeCustomPaymentModal(){
        $('#customPaymentModal').modal('hide');
    }

    $( "#button-payment" ).on( "click", function() {
        $('#customPaymentModal').modal('hide');
        var winFeature =
            'location=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes';
        window.open('ccbillform.php','null',winFeature);
    } );


    $('.method').click(function (ev) {
        ev.preventDefault();
        $(this).addClass('active').siblings().removeClass('active');
        var gateway = $(this).attr('data-gateway-key');
        $("input[name=paymentOption][value='"+gateway+"']").prop("checked",true);
        var checkCP = $(this).attr('data-custom-payment');
        if(checkCP == 1){

            $('[data-proof-form="sent"]').hide();
            $('[data-proof-form="send"]').show();

            $('#button-custom-payment').addClass('btn-success');
            $('#button-custom-payment').removeClass('btn-secondary');
            $('#button-custom-payment').text('Submit Payment Proof');

            $('#customPaymentModal').modal('show');
            var description = $(this).attr('data-gateway-desc');
            var reviewTime = $(this).attr('data-gateway-review-time');
            var externalUrl = $(this).attr('data-gateway-external-url');
            var p_logo = $(this).attr('data-gateway-logo');
            var proof = $(this).attr('data-gateway-proof');

            var pending = $(this).attr('data-gateway-pending');
            var order = $(this).attr('data-gateway-order');
            if(pending == 'Yes'){
                $('[data-proof-form="sent"]').show();
                $('[data-proof-form="send"]').hide();
                $('#paymentOrderId').text(order);
            }
            if(p_logo == 'No'){
                $('.customPaymentLogo').hide();
            } else {
                $('.customPaymentLogo').show();
                $('.customPaymentLogoImg').attr('src',p_logo);
            }
            if(externalUrl == 'No'){
                $('[data-custom-payment-external-url]').hide();
                $('[data-custom-payment-desc]').show();
            } else {
                $('#button-custom-payment-external-url').attr('href',externalUrl);
                $('[data-custom-payment-external-url]').show();
                $('[data-custom-payment-desc]').hide();
            }
            $('#customPaymentNameGateway').val(gateway);
            $('#customPaymentTitle').text('<?= $sm['lang'][1031]['text']; ?> '+gateway);
            $('#customPaymentName').text(gateway);
            $('#customPaymentProof').attr('placeholder',proof);

            $('#customPaymentInstructions').text(description);
            $('.customPaymentReviewTime').text(reviewTime +'hs');
            $("#customPayment").show();
            $("#button-payment").hide();
             $("#lwPaymentAmount").hide();
        } else {
            $("#customPayment").hide();
            $("#button-payment").show();
            $("#lwPaymentAmount").show();
            updatePaymentGateway(gateway);
        }

    });

    var paymentType = '<?= $type; ?>';
    $("#cardCheckoutForm").hide();

    var gatewayConfiguration = <?= json_encode($gatewayConfiguration) ?>,
    userDetails = <?= json_encode($userDetails) ?>;

    <?php if(isset($_GET['gateway'])){ ?>
        var gatewayName = '<?= secureEncode($_GET['gateway']);?>';
        var selectedPayment = $( '[data-gateway="'+gatewayName+'"').val();
        paymentGateway = selectedPayment;
        var gatewayCurrency = gatewayConfiguration[selectedPayment]['currency'],
            currencySymbol = gatewayConfiguration[selectedPayment]['currencySymbol'],
            formattedAmount = '<hr>' + currencySymbol + ' ' + userDetails['amounts'][gatewayCurrency] + ' ' + gatewayCurrency + '<hr>';
        $('#lwPaymentAmount').html(formattedAmount);
        if (selectedPayment == 'iyzico' || selectedPayment == 'authorize-net') {
            $("#cardCheckoutForm").show();
            $("#cname").attr("required","true");
            $("#cardNumber").attr("required","true");
            $("#expmonth").attr("required","true");
            $("#expyear").attr("required","true");
            $("#cvv").attr("required","true");
        } else {
            $("#cardCheckoutForm").hide();
            $("#cname").removeAttr("required","true");
            $("#cardNumber").removeAttr("required","true");
            $("#expmonth").removeAttr("required","true");
            $("#expyear").removeAttr("required","true");
            $("#cvv").removeAttr("required","true");
        }
    <?php } ?>

    function updatePaymentGateway(gateway){
        var selectedPayment = gateway;
        paymentGateway = selectedPayment;
        var gatewayCurrency = gatewayConfiguration[selectedPayment]['currency'],
            currencySymbol = gatewayConfiguration[selectedPayment]['currencySymbol'],
            formattedAmount = '<hr>' + currencySymbol + ' ' + userDetails['amounts'][gatewayCurrency] + ' ' + gatewayCurrency + '<hr>';
        $('#lwPaymentAmount').html(formattedAmount);
        if (selectedPayment == 'iyzico' || selectedPayment == 'authorize-net') {
            $("#cardCheckoutForm").show();
            $("#cname").attr("required","true");
            $("#cardNumber").attr("required","true");
            $("#expmonth").attr("required","true");
            $("#expyear").attr("required","true");
            $("#cvv").attr("required","true");
        } else {
            $("#cardCheckoutForm").hide();
            $("#cname").removeAttr("required","true");
            $("#cardNumber").removeAttr("required","true");
            $("#expmonth").removeAttr("required","true");
            $("#expyear").removeAttr("required","true");
            $("#cvv").removeAttr("required","true");
        }
    }
</script>

<script type="text/javascript">

    $(document).ready(function(){
        var max_upload = <?= $sm['config']['max_upload']; ?>;
        $("#upload-proof").dmUploader({
            url: '<?=$sm['config']['site_url']; ?>assets/sources/upload.php',
            multiple: false,
            onFileExtError: function(file){
            },
            onNewFile: function(id, file){
                console.log(id);

                var maxAllowed = max_upload / 1024 / 1024;
                console.log(maxAllowed);
                if(file.size > max_upload){
                    var data = [];
                    data.name = '';
                    data.icon = '<?= $sm['user']['profile_photo']; ?>';
                    data.message = '<?= $sm['lang'][809]['text']; ?> ('+maxAllowed+') MB';
                    if(mobileSite){
                        pushNotifMobile(data,1);
                    } else {
                        swal({   title: '<?= $sm['lang'][809]['text']; ?>', text: '<?= $sm['lang'][809]['text']; ?> ('+maxAllowed+') MB',   type: 'info' }, function(){ });
                    }
                    return false;
                }
                var fileUrl = URL.createObjectURL(file);
                $('#proof_image_progress').show();
                $('#button-custom-payment').removeClass('btn-success');
                $('#button-custom-payment').addClass('btn-secondary');
            },
            onUploadProgress: function(id,percent){
                console.log(percent+'%');
                $('#proof_image_progress_bar').css('width',percent+'%');
                $('#proof_image_progress_bar').text(percent+'%');
            },
            onComplete: function(){
                $('#proof_image_progress_bar').text('<?= $sm['lang'][1032]['text']; ?>');
                $('#button-custom-payment').addClass('btn-success');
                $('#button-custom-payment').removeClass('btn-secondary');
            },
            onUploadSuccess: function(id, file){
            $('#proof_image').val(file.path);
          }
        });

        $('#submitCustomPayment').on('submit', function(e){
            e.preventDefault();
            if($('#button-custom-payment').hasClass('btn-secondary')){
                return false;
            } else {

                $('#button-custom-payment').removeClass('btn-success');
                $('#button-custom-payment').addClass('btn-secondary');
                $('#button-custom-payment').text('<?= $sm['lang'][1033]['text']; ?>');
                $.ajax({
                    type: "POST",
                    url: "<?=$sm['config']['site_url']; ?>requests/api.php",
                    data: $(this).serialize(),
                    dataType:'JSON',
                    success: function(response){
                        $('#paymentOrderId').text(response.order);
                        $('[data-proof-form="sent"]').show();
                        $('[data-proof-form="send"]').hide();
                    }
                });
            }
        });

        $('#lwPaymentForm').on('submit', function(e){
            e.preventDefault();

            if(paymentGateway == ''){
                alert('<?= $sm['lang'][881]['text']; ?>');
                return false;
            }
            var paymentOption = paymentGateway;

            if(paymentOption == 'paypal'){
                if(paymentType == 'premium'){
                    $('#paypalSubscribe').submit();
                } else {
                    $('#paypalForm').submit();
                }
                return false;
            }


            <?php if($type == 'credits'){ ?>
                if(paymentOption == 'fortumo'){
                    var name = '<?= $paypalItemName; ?>';
                    var encode = 'amount=<?= $packageArray[$package]['credits']; ?>callback_url=<?=$sm['config']['site_url']; ?>credits-okcredit_name='+name+'cuid=<?=$sm['user']['id']; ?>currency=<?=$sm['config']['currency']; ?>display_type=userprice=<?=$packageArray[$package]['price'];?>v=web';
                    $.ajax({
                        type: "GET",
                        url: "<?=$sm['config']['site_url']; ?>requests/api.php",
                        data: {
                            action: 'fortumo',
                            encode: encode
                        },
                        dataType:'JSON',
                        success: function(response){
                            var md5 = response['encode'];
                            var callback = encodeURI('<?=$sm['config']['site_url']; ?>credits-ok');
                            name = encodeURI(name);
                            var href= 'http://pay.fortumo.com/mobile_payments/<?=$sm['plugins']['fortumo']['id']; ?>?amount=<?= $packageArray[$package]['credits']; ?>&callback_url=<?=$sm['config']['site_url']; ?>credits-ok&credit_name='+name+'&cuid=<?=$sm['user']['id']; ?>&currency=<?=$sm['config']['currency']; ?>&display_type=user&price=<?=$packageArray[$package]['price'];?>&v=web&sig='+md5;
                            window.open(href,'popUpWindow','_self');
                        }
                    });
                    return false;
                }
            <?php } ?>


            // Paypal, Paytm, Instamojo or iyzico script for send ajax request to server side start
            if (paymentOption == 'paytm' || paymentOption == 'instamojo' || paymentOption == 'iyzico' || paymentOption == 'authorize-net' || paymentOption == 'bitpay' || paymentOption == 'mercadopago' || paymentOption == 'payumoney' || paymentOption == 'mollie') {

                //show loader before ajax request
                $(".lw-show-till-loading").show();

                //send ajax request with form data to server side processing
                $.ajax({
                    type: 'post', //form method
                    context: this,
                    url: 'payment-process.php', // post data url
                    dataType: "JSON",
                    data: $('#lwPaymentForm').serialize() + '&' + $.param(JSON.parse('<?php echo json_encode($userDetails) ?>')), // form serialize data
                    error: function (err) {
                        var error = err.responseText
                            string = '';

                        //on error show alert message
                        string += '<div class="alert alert-danger" role="alert">'+err.responseText+'</div>';

                        $('#lwValidationMessage').html(string);
                        //alert("AJAX error in request: " + JSON.stringify(err.responseText, null, 2));

                        //hide loader after ajax request complete
                        $(".lw-show-till-loading").hide();
                    },
                    success: function (response) {

                       //check server side validation
                        if (typeof(response.validationMessage)) {

                            var messageData = [],
                                string = '';

                            //validation message
                            $.each(response.validationMessage, function(index, value) {
                                messageData = value;
                                string += '<div class="alert alert-danger" role="alert">'+messageData+'</div>';
                            });

                            //print error mesaages
                            $('#lwValidationMessage').html(string);

                            //hide loader after ajax request complete
                            $(".lw-show-till-loading").hide();
                        }

                        //load paytm merchant form
                        if (response.paymentOption == "paytm") {

                            $('body').html(response.merchantForm);

                        } else if(response.paymentOption == "instamojo") {

                            //on success load instamojo long url page else show error message
                            if (response.message == 'success') {
                                //show loader before page load
                                $(".lw-show-till-loading").show();

                                console.log(response);
                                return false;
                                window.location.href = response.longurl;
                            } else {
                                //error message
                                string += '<div class="alert alert-danger" role="alert">'+response.errorMessage+'</div>';

                                //print error mesaages
                                $('#lwValidationMessage').html(string);
                            }

                        } else if(response.paymentOption == "iyzico") {

                            //on success load html content page on iyzico else show error message
                            if (response.status == 'success') {
                                $('body').html(response.htmlContent);

                            } else if (response.message == 'failed') {

                                string += '<div class="alert alert-danger" role="alert">'+response.errorMessage+'</div>';
                            }

                            else {
                                //error message
                                //validation message
                                $.each(response.validationMessage, function(index, value) {
                                    messageData = value;
                                    string += '<div class="alert alert-danger" role="alert">'+messageData+'</div>';
                                });
                            }
                            //print error mesaages
                            $('#lwValidationMessage').html(string);

                        } else if(response.paymentOption == "paypal") {

                            //show loader before page load
                            $(".lw-show-till-loading").show();

                           //on success load paypalUrl page
                            window.location.href = response.paypalUrl;
                        } else if (response.paymentOption == 'authorize-net') {
                            var authorizeNetCallbackUrl = <?php echo json_encode($authorizeNetCallbackUrl); ?>;
                            if (response.status == "success") {
                                $('body').html("<form action='"+authorizeNetCallbackUrl+"' method='post'><input type='hidden' name='response' value='"+JSON.stringify(response)+"'><input type='hidden' name='paymentOption' value='authorize-net'></form>");
                                $('body form').submit();
                            } else if (response.status == "error") {
                                string = response.message;
                            } else {
                                $.each(response.validationMessage, function(index, value) {
                                    messageData = value;
                                    string += '<div class="alert alert-danger" role="alert">'+messageData+'</div>';
                                });
                            }
                            $('#lwValidationMessage').html(string);
                        } else if (response.paymentOption == 'bitpay') {
                            if (response.status == "success") {
                                window.location.href = response.invoiceUrl;
                            } else {
                                $('#lwValidationMessage').html('Something went wrong on server.');
                                $(".lw-show-till-loading").hide();
                            }
                        } else if (response.paymentOption == 'mercadopago') {
                            if (response.status == 'success') {
                                window.location.href = response.redirect_url;
                            } else if (response.status == 'error') {
                                $(".lw-show-till-loading").hide();
                                var string = '';
                                //on error show alert message
                                string += '<div class="alert alert-danger" role="alert">'+response.message+'</div>';
                                $('#lwValidationMessage').html(string);
                            }
                        } else if (response.paymentOption == 'payumoney') {
                            bolt.launch(response, {
                                responseHandler: function(BOLT) {},
                                catchException: function(BOLT) {
                                    $(".lw-show-till-loading").hide();
                                    var string = '';
                                    //on error show alert message
                                    string += '<div class="alert alert-danger" role="alert">'+BOLT.message+'</div>';
                                    $('#lwValidationMessage').html(string);
                                }
                            });
                        } else if (response.paymentOption == "mollie") {
                            if (response.message == 'success') {
                                 window.location.href = response.checkoutUrl;
                            } else if (response.message == 'failed') {
                                $(".lw-show-till-loading").hide();
                                var string = '';
                                //on error show alert message
                                string += '<div class="alert alert-danger" role="alert">'+response.errorMessage+'</div>';
                                $('#lwValidationMessage').html(string);
                            }
                        }
                    }
                });
            // Paypal, Paytm, Instamojo or iyzico script for send ajax request to server side end

            // Paystack script for send ajax request to server side start
            } else if (paymentOption == 'paystack') {

                //config data
                var configData = <?php echo json_encode($config); ?>,
                    paymentPagePath = <?php echo json_encode($paymentPagePath); ?>,
                    configItem = configData['payments']['gateway_configuration']['paystack'],
                    paystackCallbackUrl = configItem.callbackUrl,
                    userDetails = <?php echo json_encode($userDetails); ?>;

                    const amount =  userDetails['amounts'][configItem['currency']];

                var paystackPublicKey = '';

                //check paystack test or production mode
                if (configItem['testMode']) {
                    paystackPublicKey = configItem['paystackTestingPublicKey'];
                } else {
                    paystackPublicKey = configItem['paystackLivePublicKey'];
                }

                var paystackAmount = amount.toFixed(2) * 100,
                    handler = PaystackPop.setup({
                    key: paystackPublicKey, // add paystack public key
                    email: userDetails['payer_email'], // add customer email
                    amount: paystackAmount, // add order amount
                    currency: configItem['currency'], // add currency
                    callback: function(response){
                        // after successful paid amount return paystack reference Id
                        var paystackReferenceId = response.reference;

                        //show loader before ajax request
                        $(".lw-show-till-loading").show();

                        var paystackData = {
                            'paystackReferenceId': paystackReferenceId,
                            'paystackAmount': paystackAmount
                        };

                        var paystackData = $('#lwPaymentForm').serialize() + '&' + $.param(userDetails) + '&' + $.param(paystackData);

                        $.ajax({
                            type: 'post', //form method
                            context: this,
                            url: 'payment-process.php', // post data url
                            dataType: "JSON",
                            data: paystackData, // form serialize data
                            error: function (err) {
                                var error = err.responseText
                                    string = '';

                                //on error show alert message
                                string += '<div class="alert alert-danger" role="alert">'+err.responseText+'</div>';

                                $('#lwValidationMessage').html(string);
                                //alert("AJAX error in request: " + JSON.stringify(err.responseText, null, 2));

                                //hide loader after ajax request complete
                                $(".lw-show-till-loading").hide();
                            },
                            success: function (response) {
                                if (response.status == true) {
                                    $('body').html("<form action='"+paystackCallbackUrl+"' method='post'><input type='hidden' name='response' value='"+JSON.stringify(response)+"'><input type='hidden' name='paymentOption' value='paystack'></form>");
                                    $('body form').submit();
                                }
                            }
                        });

                    },
                    onClose: function(){
                        //on close paystack inline widget then load back to checkout form page
                       // window.location.href = paymentPagePath;
                    }
                });

                //open paystack inline widen using iframe
                handler.openIframe();
            // Paystack script for send ajax request to server side end


            // Stripe script for send ajax request to server side start
            } else if (paymentOption == 'stripe') {

                //config data
                var configData = <?php echo json_encode($config); ?>,
                    configItem = configData['payments']['gateway_configuration']['stripe'],
                    userDetails = <?php echo json_encode($userDetails); ?>,
                    stripePublishKey  = '';
                $(".lw-show-till-loading").show();

                //check stripe test or production mode
                if (configItem['testMode']) {
                    stripePublishKey = configItem['stripeTestingPublishKey'];
                } else {
                    stripePublishKey = configItem['stripeLivePublishKey'];
                }

                userDetails['paymentOption'] = paymentOption;

                // Stripe script for send ajax request to server side start
                $.ajax({
                    type: 'post', //form method
                    context: this,
                    url: 'payment-process.php', // post data url
                    dataType: "JSON",
                    data: userDetails, // form serialize data
                    error: function (err) {
                        var error = err.responseText
                            string = '';
                        console.log(err);
                        //on error show alert message
                        string += '<div class="alert alert-danger" role="alert">'+err.responseText+'</div>';

                        $('#lwValidationMessage').html(string);
                        //alert("AJAX error in request: " + JSON.stringify(err.responseText, null, 2));

                        //hide loader after ajax request complete
                        $(".lw-show-till-loading").hide();
                    },
                    success: function (response) {
                        var stripe = Stripe(stripePublishKey);
                        if (response.message == "failed")  {
                            $(".lw-show-till-loading").hide();
                            var string = '';
                            //on error show alert message
                            string += '<div class="alert alert-danger" role="alert">'+response.errorMessage+'</div>';
                            $('#lwValidationMessage').html(string);
                        } else {
                            console.log(response);
                            stripe.redirectToCheckout({
                                sessionId: response.id,
                            }).then(function (result) {
                                // If `redirectToCheckout` fails due to a browser or network
                                // error, display the localized error message to your customer
                                // using `result.error.message`.
                                var string = '';
                                //on error show alert message
                                string += '<div class="alert alert-danger" role="alert">'+result.error.message+'</div>';

                                $('#lwValidationMessage').html(string);
                            });
                        }
                    }
                });

            // Razorpay script for send ajax request to server side start
            } else if (paymentOption == 'razorpay') {

                //config data
                var configData = <?php echo json_encode($config); ?>,
                    razorpayCallbackUrl = <?php echo json_encode($razorpayCallbackUrl); ?>,
                    paymentPagePath = <?php echo json_encode($paymentPagePath); ?>,
                    configItem = configData['payments']['gateway_configuration']['razorpay'],
                    userDetails = <?php echo json_encode($userDetails); ?>,
                    razorpayKeyId = '';

                const amount =  userDetails['amounts'][configItem['currency']];

                //check razorpay test or production mode
                if (configItem['testMode']) {
                    razorpayKeyId = configItem['razorpayTestingkeyId'];
                } else {
                    razorpayKeyId = configItem['razorpayLivekeyId'];
                }

                var razorpayAmount = amount.toFixed(2) * 100,
                    razorpayPaymentId = null,
                    options = {
                    "key": razorpayKeyId, // add razorpay Api Key Id
                    "amount": razorpayAmount, // 2000 paisa = INR 20
                    "currency": configItem['currency'], // add currency
                    "name": configItem['merchantname'], // add merchant user name
                    "handler": function (response){
                        // after successful paid amount return razorpay payment Id
                        razorpayPaymentId = response.razorpay_payment_id;
                        var encodeRazorpayAmount =  window.btoa(razorpayAmount);
                        //show loader before ajax request
                        $(".lw-show-till-loading").show();

                        var razorpayData = {
                            'razorpayPaymentId': razorpayPaymentId,
                            'razorpayAmount': encodeRazorpayAmount
                        };

                        var razorpayData = $('#lwPaymentForm').serialize() + '&' + $.param(userDetails) + '&' + $.param(razorpayData);

                        $.ajax({
                            type: 'post', //form method
                            context: this,
                            url: 'payment-process.php', // post data url
                            dataType: "JSON",
                            data: razorpayData, // form serialize data
                            error: function (err) {
                                var error = err.responseText
                                    string = '';

                                //on error show alert message
                                string += '<div class="alert alert-danger" role="alert">'+err.responseText+'</div>';

                                $('#lwValidationMessage').html(string);
                                //alert("AJAX error in request: " + JSON.stringify(err.responseText, null, 2));

                                //hide loader after ajax request complete
                                $(".lw-show-till-loading").hide();
                            },
                            success: function (response) {
                                if (response.status == "captured") {
                                    razorpayCallbackUrl = razorpayCallbackUrl+'?orderId='+userDetails['order_id'];
                                    $('body').html("<form action='"+razorpayCallbackUrl+"' method='post'><input type='hidden' name='response' value='"+JSON.stringify(response)+"'><input type='hidden' name='paymentOption' value='razorpay'></form>");
                                    $('body form').submit();
                                }
                            }
                        });

                        //after successful payment go to response page
                        /* window.location.href = razorpayCallbackUrl+'?paymentOption='+paymentOption+'&razorpayPaymentId='+razorpayPaymentId+'&amount='+encodeRazorpayAmount; */
                    },
                    "prefill": {
                        "name": userDetails['payer_name'], // add user name
                        "email": userDetails['payer_email'], // add user email
                    },
                    "theme": {
                        "color": configItem['themeColor'], // add widget theme color
                    },
                    "modal": {
                        "ondismiss": function(e){
                            // if razorpay payment Id is not received on onDismiss razorpay inline widget then load Url back to checkout form page
                            if (razorpayPaymentId == null) {
                                //window.location.href = paymentPagePath;
                            }
                        }
                    }
                };
                var rzp1 = new Razorpay(options);
                rzp1.open();
            }
        });
    });
</script>
<!-- /  Jquery Form submit in script tag -->

<?php
if (getArrayItem($paystackConfigData, 'enable', false)) { ?>
    <!-- load paystack inline widget script -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <!-- / load Paystack inline widget script -->
<?php } ?>

<?php
if (getArrayItem($stripeConfigData, 'enable', false)) { ?>
    <!-- load stripe inline widget script -->
    <script src="https://js.stripe.com/v3"></script>
    <!-- load stripe inline widget script -->
<?php } ?>

<?php
if (getArrayItem($razorpayConfigData, 'enable', false)) { ?>
    <!-- load razorpay inline widget script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- load razorpay inline widget script -->
<?php } ?>

<?php
if (getArrayItem($payUmoneyConfigData, 'enable', false)) {
    if (getArrayItem($payUmoneyConfigData, 'testMode') == true) { ?>
        <script id="bolt" src="https://sboxcheckout-static.citruspay.com/bolt/run/bolt.min.js" bolt-color="<?= getArrayItem($payUmoneyConfigData, 'checkoutColor') ?>" bolt-logo="<?= getArrayItem($payUmoneyConfigData, 'checkoutLogo') ?>"></script>
<?php } else { ?>
        <script id="bolt" src="https://checkout-static.citruspay.com/bolt/run/bolt.min.js" bolt-color="<?= getArrayItem($payUmoneyConfigData, 'checkoutColor') ?>" bolt-logo="<?= getArrayItem($payUmoneyConfigData, 'checkoutLogo') ?>"></script>
<?php } } ?>