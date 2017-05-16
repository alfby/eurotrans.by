<?php
mt_srand();
$index = mt_rand(1, 9999);
$front_messages = __('front_messages', true, false);

$theme = isset($_GET['theme']) ? $_GET['theme'] : $tpl['option_arr']['o_theme'];
?>
<div id="pjWrapperBusReservation_<?=$theme;?>">
	<div id="pjBrContainer_<?=$index; ?>" class="container-fluid pjBsContainer"></div>
</div>

<script type="text/javascript">
var pjQ = pjQ || {},
	BusReservation_<?=$index; ?>;
(function () {
	"use strict";
	var isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor),

	loadCssHack = function(url, callback){
		var link = document.createElement('link');
		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.href = url;

		document.getElementsByTagName('head')[0].appendChild(link);

		var img = document.createElement('img');
		img.onerror = function(){
			if (callback && typeof callback === "function") {
				callback();
			}
		};
		img.src = url;
	},
	loadRemote = function(url, type, callback) {
		if (type === "css" && isSafari) {
			loadCssHack(url, callback);
			return;
		}
		var _element, _type, _attr, scr, s, element;
		
		switch (type) {
		case 'css':
			_element = "link";
			_type = "text/css";
			_attr = "href";
			break;
		case 'js':
			_element = "script";
			_type = "text/javascript";
			_attr = "src";
			break;
		}
		
		scr = document.getElementsByTagName(_element);
		s = scr[scr.length - 1];
		element = document.createElement(_element);
		element.type = _type;
		if (type == "css") {
			element.rel = "stylesheet";
		}
		if (element.readyState) {
			element.onreadystatechange = function () {
				if (element.readyState == "loaded" || element.readyState == "complete") {
					element.onreadystatechange = null;
					if (callback && typeof callback === "function") {
						callback();
					}
				}
			};
		} else {
			element.onload = function () {
				if (callback && typeof callback === "function") {
					callback();
				}
			};
		}
		element[_attr] = url;
		s.parentNode.insertBefore(element, s.nextSibling);
	},
	loadScript = function (url, callback) {
		loadRemote(url, "js", callback);
	},
	loadCss = function (url, callback) {
		loadRemote(url, "css", callback);
	},
	randomString = function (length, chars) {
		var result = "";
		for (var i = length; i > 0; --i) {
			result += chars[Math.round(Math.random() * (chars.length - 1))];
		}
		return result;
	},
	getSessionId = function () {
		return sessionStorage.getItem("session_id") == null ? "" : sessionStorage.getItem("session_id");
	},
	createSessionId = function () {
		if(getSessionId()=="") {
			sessionStorage.setItem("session_id",randomString(32, "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"));
		}
	},
	options = {
		server: "<?=PJ_INSTALL_URL; ?>",
		folder: "<?=PJ_INSTALL_URL; ?>",
		index: <?=$index; ?>,
		hide: <?=isset($_GET['hide']) && (int) $_GET['hide'] === 1 ? 1 : 0; ?>,
		locale: <?=isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : $controller->pjActionGetLocale(); ?>,
		week_start: <?=(int) $tpl['option_arr']['o_week_start']; ?>,
		date_format: "<?=$tpl['option_arr']['o_date_format']; ?>",
		momentDateFormat: "<?=pjUtil::toMomemtJS($tpl['option_arr']['o_date_format']); ?>",

		message_0: "<?=pjSanitize::clean($front_messages[0]); ?>",
		message_1: "<?=pjSanitize::clean($front_messages[1]); ?>",
		message_2: "<?=pjSanitize::clean($front_messages[2]); ?>",
		message_3: "<?=pjSanitize::clean($front_messages[3]); ?>",
		message_4: "<?=pjSanitize::clean($front_messages[4]); ?>",
		message_5: "<?=pjSanitize::clean($front_messages[5]); ?>",
		
		validation:{
			required_field: "<?=pjSanitize::clean(__('front_required_field', true, false));?>",
			invalid_email: "<?=pjSanitize::clean(__('front_invalid_email', true, false));?>",
			incorrect_captcha: "<?=pjSanitize::clean(__('front_incorrect_captcha', true, false));?>",
			required_seat: "<?=pjSanitize::clean(__('front_validation_seats', true, false));?>",
			invalid_seat: "<?=pjSanitize::clean(__('front_validation_invalid_seats', true, false));?>",
			cc_expired: "<?=pjSanitize::clean(__('front_validation_cc_expired', true, false));?>"
		},

		labels:{
			seats: "<?=pjSanitize::clean(__('front_seats', true, false));?>",
			seat: "<?=pjSanitize::clean(__('front_seat', true, false));?>"
		}
	};
	if (isSafari) {
		createSessionId();
		options.session_id = getSessionId();
	}else{
		options.session_id = "";
	}
	<?php
	$dm = new pjDependencyManager(PJ_THIRD_PARTY_PATH);
	$dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();
	?>
	loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_jquery'); ?>pjQuery.min.js", function () {
		loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_validate'); ?>pjQuery.validate.min.js", function () {
			loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_validate'); ?>pjQuery.additional-methods.min.js", function () {
				loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_bootstrap'); ?>pjQuery.bootstrap.min.js", function () {
					loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_select2'); ?>pjQuery.select2.full.js", function () {
						loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_perfect_scrollbar'); ?>js/pjQuery.perfect-scrollbar.js", function () {
							loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_bootstrap_datetimepicker'); ?>moment-with-locales.min.js", function () {
								loadScript("<?=PJ_INSTALL_URL . $dm->getPath('pj_bootstrap_datetimepicker'); ?>pjQuery.bootstrap-datetimepicker.min.js", function () {
									loadScript("<?=PJ_INSTALL_URL . PJ_JS_PATH; ?>pjIntlTelInput.js", function () {
										loadScript("<?=PJ_INSTALL_URL . PJ_JS_PATH; ?>pjBusReservation.js", function () {
											BusReservation_<?=$index; ?> = new BusReservation(options);
										});
									});
								});
							});
						});
					});
				});
			});
		});
	});
})();
</script>