<?php
require_once('../assets/includes/core.php');
include 'header.php';

if($logged === false){
    header('Location:'.$sm['config']['site_url']);
    exit;
}

?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>CCBill Payment Card </title>
        <script src="assets/js/jquery-3.3.1.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="assets/css/custom.css" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Rubik" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="../themes/default/css/crossplatform.css"/>
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

            .form-select {
                background: #101010;
                border: none;
                border-radius: 5px;
                padding: 8px;
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
            input {
                color: #9b9b9b !important;
            }
            input:focus {
                background-color: #000000 !important;
                color: #9b9b9b !important;
            }
        </style>
    </head>
    <body style="background: #0a0a0a">

        <div class="mb-5 container" style="max-width: 400px" id="lwCheckoutForm">
            <div class="row">
                <div class="col-md-12" style="padding:0">

                    <form action='https://bill.ccbill.com/jpost/signup.cgi' method=POST>
                        <link rel='stylesheet' href='https://bill.dev.ccbill.com/jpost/css/button.css'>
                        <input type=hidden name=clientAccnum value='953926'>
                        <input type=hidden name=clientSubacc value='0004'>
                        <input type=hidden name=formName value='141cc'>
                        <input type=hidden name=language value='English' ><input type=hidden name=allowedTypes value='0000003226:840' ><input type=hidden name=subscriptionTypeId value='0000003226:840' >
                        <input type=submit name=submit class="btn_blue_3D"  value='Buy with CCBill'>
                    </form>

                    <form method="post">
                        <div class="card" style="border:none;background: none;">
                            <div class="card-body" style="padding:5px">


                                <h5 style="color: #FFFFFF;">Pay with Card</h5>

                                <span style="color: #FFFFFF;">$50</span>

                                <div class="form-group">
                                    <label for="cname" style="color: #FFFFFF;">Cardname:</label>
                                    <input type="text" class="form-control" id="cname" name="cardname" placeholder="" required="required">
                                </div>
                                <div class="form-group">
                                    <label for="cname" style="color: #FFFFFF;">Cardnumber:</label>
                                    <input type="text" class="form-control" id="cnumber" name="cnumber" placeholder="" required="required">
                                </div>
                                <div class="form-group">
                                    <label for="cname" style="color: #FFFFFF;">Expired Month:</label>
                                    <select name="cexpmonth" class="form-select" size="1" style="width:100%;color:#FFFFFF;" required="required">
                                        <option value="0">Please Select</option>
                                        <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cname" style="color: #FFFFFF;">Expired Year:</label>
                                    <select name="cexpyear" class="form-select" size="1" style="width:100%;color:#FFFFFF;" required="required">
                                        <option value="0">Please Select</option>
                                        <?php
                                        $aktuellesJahr = date("Y");
                                        $result = $aktuellesJahr + 50;
                                        for($i = $aktuellesJahr; $i < $result; $i++ ){
                                            ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
                                        }
                                        ?>
                                   </select>
                                </div>
                                <div class="form-group">
                                    <label for="cname" style="color: #FFFFFF;">CVV:</label>
                                    <input type="text" class="form-control" id="ccvv" name="ccvv" placeholder="" required="required">
                                </div>

                                <button type="submit" id="button-payment" value="pay" class="btn btn-block btn-success box-shadow" style="font-weight: bold;border-radius: 10px;">Pay with Card</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <script src="../themes/red/js/vendor/sweetalert.min.js"></script>
        <script src="../themes/red/js/vendor/jquery.dm-uploader.min.js"></script>
    </body>

    </html>


    <script type="text/javascript">

        $( "#button-payment" ).on( "click", function() {
           $('#customPaymentModal').modal('hide');
            var winFeature =
                'location=no,toolbar=no,menubar=no,scrollbars=yes,r
        $(document).ready(function(){

        });
    </script>
    <!-- /  Jquery Form submit in script tag -->
