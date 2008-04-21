<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.link");

require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function getSiteInfo() {
									if(document.getElementById('addForm').rss.value == '') {
										alert("<?php echo _t('RSS 주소를 입력해 주십시오.');?>\t");
										return false;		
									}
									
									if(document.getElementById('addForm').rss.value.indexOf("http://")==-1) {
										uri = 'http://'+document.getElementById('addForm').rss.value;
									} else {
										uri = document.getElementById('addForm').rss.value;
									}
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/link/site/?rss=" + uri);
									request.onVerify = function() {
										return (this.getText("/response/url") != "")
									}
									request.onSuccess = function () {
										PM.removeRequest(this);
										document.getElementById('addForm').name.value = unescape(this.getText("/response/name"));
										document.getElementById('addForm').url.value = this.getText("/response/url");
										return true;
									}
									request.onError = function () {
										PM.removeRequest(this);
										PM.showErrorMessage("<?php echo _t('RSS를 읽어올 수 없습니다.');?>","center", "bottom");
										return false;
									}
									PM.addRequest(request, "<?php echo _t('RSS를 읽어오고 있습니다.');?>");
									request.send();
								}
								
								function addLink() {
									var oForm = document.getElementById('addForm');
									trimAll(oForm);
									if (!checkValue(oForm.name, "<?php echo _t('이름을 입력해 주십시오.');?>\t")) return false;
									if (!checkValue(oForm.url, "<?php echo _t('주소를 입력해 주십시오.');?>\t")) return false;
									
									var request = new HTTPRequest("POST", blogURL + "/owner/communication/link/add/exec/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										window.location = blogURL + "/owner/communication/link";
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											case 1:
												alert("<?php echo _t('이미 존재하는 주소입니다.');?>");
												break;
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.');?>");
										}
									}
									PM.addRequest(request, "<?php echo _t('링크를 추가하고 있습니다.');?>");
									request.send("name=" + encodeURIComponent(oForm.name.value) + "&url=" + encodeURIComponent(oForm.url.value) + "&rss=" + encodeURIComponent(oForm.rss.value));
								}	
							//]]>
						</script>
						
						<div id="part-link-add" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('새로운 링크를 추가합니다');?></span></h2>

							<div class="main-explain-box">
								<p class="explain"><?php echo _t('RSS 주소를 입력해서 링크할 홈페이지의 정보를 읽어올 수 있습니다. 수동으로 제목과 주소를 입력하셔도 됩니다.');?></p>
							</div>
								
							<form id="addForm" method="post" action="<?php echo $blogURL;?>/owner/communication/link/add/">
								<div class="data-inbox">
									<dl id="rss-address-line" class="line">
										<dt><label for="rss"><?php echo _t('<acronym title="Rich Site Summary">RSS</acronym> 주소');?></label></dt>
										<dd><input type="text" id="rss" class="input-text rss" name="rss" /> <input type="button" class="get-info-button input-button" value="<?php echo _t('정보 가져오기');?>" onclick="getSiteInfo();" /></dd>
									</dl>
									<dl id="homepage-title-line" class="line">
										<dt><label for="name"><?php echo _t('홈페이지 제목');?></label></dt>
										<dd><input type="text" id="name" class="input-text name" name="name" /></dd>
									</dl>
									<dl id="homepage-address-line" class="line">
										<dt><label for="url"><?php echo _t('홈페이지 주소');?></label></dt>
										<dd><input type="text" id="url" class="input-text url" name="url" /></dd>
									</dl>
								</div>
								
								<div class="button-box">
									<input type="submit" class="add-button input-button" value="<?php echo _t('추가하기');?>" onclick="addLink(); return false" />
									<span class="hidden">|</span>
									<input type="button" class="cancel-button input-button" value="<?php echo _t('취소하기');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/communication/link'" />
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?> 