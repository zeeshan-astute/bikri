<?php
use yii\helpers\Html;
?>
<?php require_once 'adminemailheader.php';//$this->element('emailHeader'); ?>
				<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="font-family: Georgia, serif; background: #fff;" bgcolor="#ffffff">
			      <tr>
			        <td width="14" style="font-size: 0px;" bgcolor="#ffffff">&nbsp;</td>
					<td width="100%" valign="top" align="left" bgcolor="#ffffff"style="font-family: Georgia, serif; background: #fff;">
						<table cellpadding="0" cellspacing="0" border="0"  style="color: #333333; font: normal 13px Arial; margin: 0; padding: 0;" width="100%" class="content">
						<!-- <tr>
							<td style="padding: 25px 0 5px; border-bottom: 2px solid #d2b49b;font-family: Georgia, serif; "  valign="top" align="center">
								<h3 style="color:#767676; font-weight: normal; margin: 0; padding: 0; font-style: italic; line-height: 13px; font-size: 13px;">~ <currentmonthname> <currentday>, <currentyear> ~</h3>
							</td>
						</tr> -->
				

							<tr>
								<td style="padding: 15px 0;"  valign="top">
									<p style='margin-bottom: 10px'>
										<?php echo Yii::t('app','Hello'); ?> <?php echo $name; ?>,
									</p>
									<p style='margin-bottom: 10px'>
										<?php echo Yii::t('app','Admin has refunded the payment for your order'); ?>
									</p>
									<p style='margin-bottom: 10px'>
										<?php echo Yii::t('app','Order Id'); ?>: <?php echo $orderId;?>
									</p>
									<p style='margin-bottom: 10px'>
										 <?php echo Yii::t('app','If you still have any problem in accessing your account, we would like you to write your problems to'); ?>
										<?php echo $siteSettings->sitename; ?>.
									</p>


								</td>
							</tr>

							<tr>
								<td style="padding: 15px 0"  valign="top">
									<p style="color: #333333; font-weight: normal; margin: 0; padding: 0; line-height: 20px; font-size: 14px;font-family: Arial; ">
										<?php echo Yii::t('app','Regards,'); ?>
										<br />
										<b><?php echo $siteSettings->sitename.' '.Yii::t('app','Team'); ?>.</b>
									</p>
									<br>
								</td>
							</tr>
						</table>
					</td>
					<td width="16" bgcolor="#ffffff" style="font-size: 0px;font-family: Georgia, serif; background: #fff;">&nbsp;</td>
			      </tr>
				</table><!-- body -->
				  <?php require_once 'emailfooter.php';//$this->element('emailFooter'); ?>
		  	</td>
		</tr>
    </table>
  </body>
</html>
<?php //die; ?>
