<?php
use yii\helpers\Html;
?>
<?php require_once 'emailheader.php';//$this->renderPartial('emailheader',array('siteSettings'=>$siteSettings));
?>
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
							<td style="padding: 18px 0 0;" align="left">
								<h2 style=" font-weight: normal; margin: 0; padding: 0 0 12px; font-style: inherit; line-height: 30px; font-size: 25px;
									font-family: Trebuchet MS; border-bottom: 1px solid #333333; ">
									<?php echo Yii::t('app','Your product has been promoted'); ?>
								</h2>
							</td>
						</tr>

							<tr>
								<td style="padding: 15px 0;"  valign="top">
									<p style='margin-bottom: 10px'>
										 <?php echo Yii::t('app','Hi'); ?><?php echo ' '.$sellerName; ?>,
									</p>

									<p style='margin-bottom: 10px'>
										<?php echo Yii::t('app','Your product has been promoted successfully.');?>
									</p>
									<p style='margin-bottom: 10px'>
										<?php echo Yii::t('app','Product :');?>
										<a style="text-decoration: none;" href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('products').'/view/'.yii::$app->Myclass->safe_b64encode($productModel->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($productModel->name); ?>">
										<?php echo $productName;?>
										</a>
									<a style="text-decoration: none;" href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('products').'/'.yii::$app->Myclass->safe_b64encode($productModel->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($productModel->name); ?>">
										<?php echo $productName;?>
									</a>
									</p>
									<p style='margin-bottom: 10px'>
										<?php echo Yii::t('app','Happy selling !!!'); ?>
									</p>
								</td>
							</tr>

							<tr>
								<td style="padding: 15px 0"  valign="top">
									<p style="color: #333333; font-weight: normal; margin: 0; padding: 0; line-height: 20px;
										font-size: 14px;font-family: Arial; ">
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
				  <?php require_once 'emailfooter.php';//$this->renderPartial('emailfooter',array('siteSettings'=>$siteSettings)); ?>
		  	</td>
		</tr>
    </table>
  </body>
</html>