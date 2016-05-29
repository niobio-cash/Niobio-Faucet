<?php
ini_set('max_execution_time', 20);
require_once 'classes/jsonRPCClient.php';
require_once 'classes/recaptcha.php';
require_once 'config.php';

?><!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title><?php echo $faucetTitle; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">




  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">

  <script>var isAdBlockActive=true;</script>
  <script src="js/advertisement.js"></script>
  <script>
  if (isAdBlockActive) { 
    window.location = "./adblocker.php"
  }
  </script>


</head>

<body>

  <div class="container">

    <div id="login-form">

	  
	  <h3><a href="./"><img src="<?php echo $logo; ?>" height="256"></a><br /><br /> <?php echo $faucetSubtitle; ?></h3>
	  
	  
      <fieldset>

        <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
        <?php // <a href="https://hashflare.io/r/69295B0A-ads"><img src="https://hashflare.io/banners/468x60-eng-2.jpg" alt="HashFlare"></a> ?>
        <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

        <br />


          <?php                  

        $bitcoin = new jsonRPCClient('http://127.0.0.1:32344/json_rpc');

        $balance = $bitcoin->getbalance();
        $balanceDisponible = $balance['available_balance'];
        $lockedBalance = $balance['locked_amount'];
        $dividirEntre = 1000000000000;
        $totalBCN =  ($balanceDisponible+$lockedBalance)/$dividirEntre;
        

        $recaptcha = new Recaptcha($keys);
        //Available Balance
        $balanceDisponibleFaucet = number_format(round($balanceDisponible/$dividirEntre,12),12,'.', '');
        ?>

        <form action="request.php" method="POST">

          <?php if(isset($_GET['msg'])){
            $mensaje = $_GET['msg']; 

            if($mensaje == "captcha"){?>
            <div  id="alert" class="alert alert-error radius">
              Неправильна Captcha, введіть правильну.
            </div>
            <?php }else if($mensaje == "wallet"){ ?>

            <div id="alert" class="alert alert-error radius">
              Введіть правильну адресу карбованців.
            </div>
            <?php }else if($mensaje == "success"){ ?>

            <div class="alert alert-success radius">
              Ви виграли <?php echo $_GET['amount']; ?> крб.<br/><br/>
              Ви отримаєте <?php echo $_GET['amount']-0.0001; ?> крб. (Комісія мережі 0.0001)<br/>
              <a target="_blank" href="http://52.21.253.162/?hash=<?php echo $_GET['txid']; ?>#blockchain_transaction">Перевірте у блокчейні.</a>
            </div>
            <?php }else if($mensaje == "paymentID"){ ?>

            <div id="alert" class="alert alert-error radius">
              Перевірте ваш payment ID. <br>Він повинен складатись з 64 знаків без спецсимволів.
            </div>
            <?php }else if($mensaje == "notYet"){ ?>

            <div id="alert" class="alert alert-warning radius">
              Карбованці видаються раз на 12 годин. Зайдіть пізніше.
            </div>
            <?php } ?>

            <?php } ?>
            <div class="alert alert-info radius">
              У нас є: <?php echo $balanceDisponibleFaucet ?> крб.<br>
              <?php

              $link = mysqli_connect($hostDB, $userDB, $passwordDB, $database);

              $query = "SELECT SUM(payout_amount) FROM `payouts`;";

              $result = mysqli_query($link, $query);
              $dato = mysqli_fetch_array($result);

              $query2 = "SELECT COUNT(*) FROM `payouts`;";

              $result2 = mysqli_query($link, $query2);
              $dato2 = mysqli_fetch_array($result2);



              mysqli_close($link);
              ?>

              Ми вже виплатили: <?php echo $dato[0]/$dividirEntre; ?> крб. за <?php echo $dato2[0];?> виплат.
            </div>

            <?php if($balanceDisponibleFaucet<6.1){ ?>
            <div class="alert alert-warning radius">
             Кран порожній або баланс менший ніж виграш. <br> Зайдіть пізніше, &ndash; може хтось пожертвує нам трохи карбованців.
           </div>

           <?php }else{?>

           <input type="text" name="wallet" required placeholder="Адреса карбованців">

           <input type="text" name="paymentid" placeholder="Payment ID (Необов'язково)" >
           <br/>
           <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
           <iframe data-aa='74112' src='https://ad.a-ads.com/74112?size=468x60' scrolling='no' style='width:468px; height:60px; border:0px; padding:0;overflow:hidden' allowtransparency='true' frameborder='0'></iframe>
           <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
           <br/>
           <?php 
           echo $recaptcha->render();     
           ?>

           <center><input type="submit" value="Отримати безкоштовні карбованці!"></center>
           <br>
           <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
           <iframe scrolling="no" frameborder="0" style="overflow:hidden;width:468px;height:60px;" src="//bee-ads.com/ad.php?id=6534"></iframe>
           <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
           <?php } ?>
           <br>
		     <?php /*
           <div class="table-responsive">
            <table class="table table-bordered table-condensed">
              <thead>
                <tr>
                  <th><h6><b>Cleared Sites</b><br> <small>Sites that have their wallets allowed to request more than 1 time but only with a different payment id.</small></h6></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($clearedAddresses as $key => $item) {
                  echo "<tr>
                  <th>".$key."</th>
                  </tr>";

                }?>
              </tbody>
            </table>
          </div>
*/?>

          <div class="table-responsive">
            <h6><b>Останні 5 поповнень</b></h6>
            <table class="table table-bordered table-condensed">
              <thead>
                <tr>
                  <th>Дата</th>
                  <th>Сума</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $deposits = ($bitcoin->get_transfers());

                $transfers = array_reverse(($deposits["transfers"]),true);
                $contador = 0;
                foreach($transfers as $deposit){
                  if($deposit["output"] == ""){
                    if($contador < 6){
                      $time = $deposit["time"];
                      echo "<tr>";
                      echo "<th>".gmdate("Y-m-d H:i:s", $time)."</th>";
                      echo "<th>".round($deposit["amount"]/$dividirEntre,8)."</th>";
                      echo "</tr>";
                      $contador++;
                    }
                  }


                }
                ?>
              </tbody>
            </table>
          </div>
          <p style="font-size:10px;">Пожертвуйте карбованці для підтримки цього крану. <br>Адреса: <?php echo $faucetAddress; ?><br>&#169; 2015 Faucet by Ratnet</p></center>
          <footer class="clearfix">
          </footer>
        </form>

      </fieldset>

    </div> <!-- end login-form -->

  </div>


  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <?php if(isset($_GET['msg'])) { ?>
  <script>
  setTimeout( function(){ 
    $( "#alert" ).fadeOut(3000, function() {
    });
  }  , 10000 );
  </script>
  <?php } ?>


</body>
</html>