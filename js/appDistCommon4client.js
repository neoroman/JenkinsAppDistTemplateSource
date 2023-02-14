function DropDownChanged(oDDL) {
	// var oTextbox = oDDL.form.elements["version_txt"];
	// if (oTextbox) {
	//     oTextbox.style.display = (oDDL.value == "") ? "" : "none";
	//     if (oDDL.value == "")
	//         oTextbox.focus();
	// }
	var oForm = document.getElementById('mySearch')
	var oHidden = oForm.elements["version"];
	var oDDL = oForm.elements["version_ddl"];
	var oTextbox = oForm.elements["version_txt"];
	if (oHidden && oDDL && oTextbox) {
		oHidden.value = (oDDL.value == "self") ? oTextbox.value : oDDL.value;
		if (oDDL.value != "self") {
			oForm.submit();
		}
	}
}
function FormSubmit(oForm) {
		var oHidden = oForm.elements["version"];
		var oDDL = oForm.elements["version_ddl"];
		var oTextbox = oForm.elements["version_txt"];
		if (oHidden && oDDL && oTextbox) {
			oHidden.value = (oDDL.value == "self") ? oTextbox.value : oDDL.value;
		}
}
function FormSubmitWithKeyword(aKey) {
		var oForm = document.getElementById('mySearch')
		var oHidden = oForm.elements["version"];
		if (oHidden && aKey) {
			oHidden.value = aKey;
			oForm.submit();
		}
}
function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, '\\$&');
	var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, ' '));
}
function passingQueryTo(url, isReplace) {
	var pathArray = window.location.pathname.split('/');
	var newPathname = "";
	if (url.indexOf('.')) {
		// 상태 패스인 경우
		for (i = 0; i < pathArray.length; i++) {
			if (filenameWithExtension != pathArray[i]) {
				newPathname += pathArray[i];
				newPathname += "/";
			}
		}

		newPathname = newPathname + url;
	}
	else {
		newPathname = url;
	}

	if (url.indexOf('?') > -1) {
		if (isReplace) {
			location.replace(newPathname + "&accessToken=" + getParameterByName("accessToken"));
		} else {
			window.location.href = newPathname + "&accessToken=" + getParameterByName("accessToken");
		}
	}
	else {
		if (isReplace) {
			location.replace(newPathname + "?accessToken=" + getParameterByName("accessToken"));
		} else {
			window.location.href = newPathname + "?accessToken=" + getParameterByName("accessToken");
		}
	}
}

var MD5 = function(d){result = M(V(Y(X(d),8*d.length)));return result.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}

Date.prototype.yyyymmdd = function() {
	var mm = this.getMonth() + 1; // getMonth() is zero-based
	var dd = this.getDate();

	return [this.getFullYear(),
		(mm>9 ? '' : '0') + mm,
		(dd>9 ? '' : '0') + dd
	].join('');
};

///////////////////////////////////////////////////////////////////////////
// function for Browser OS(Platform)
///////////////////////////////////////////////////////////////////////////
function getMobileOperatingSystem() {
	var userAgent = navigator.userAgent || navigator.vendor || window.opera;
	// Windows Phone must come first because its UA also contains “Android”
	if (/windows phone/i.test(userAgent)) {
		return "Windows Phone";
	}
	if (/android/i.test(userAgent)) {
		return "Android";
	}
	// iOS detection from: http://stackoverflow.com/a/9039885/177710
	if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
		return "iOS";
	}
	const ua = userAgent.toLowerCase();
	const isiPad = ua.indexOf('ipad') > -1 || ua.indexOf('macintosh') > -1 && 'ontouchend' in document;
	if (isiPad) {
		return "iOS";
	}
	return "unknown";
}
function basename(path) {
	return path.split('/').reverse()[0];
}
///////////////////////////////////////
// for iOS download only in PC Browser
function appDownloader(url) {
	if (url) {
		if (url.startsWith("itms-services://") && getMobileOperatingSystem() != "iOS" ) {
			var plistFile = url.substring(46); // get-rid-of ``itms-services://?action=download-manifest&url=''
			var finalUrl = plistFile.split('.').slice(0,-1).join('.')
			var userAgent = navigator.userAgent || navigator.vendor || window.opera;
			var fileName = basename(url);
			var result = confirm("[ Download > " + fileName + " ]\n\n다운로드된 IPA로는 설치하실 수 없습니다.\n\n그래도 받으시겠습니까?\n\n\n\n(USER Agent: " + userAgent + ")");
			if (result == true) {
				window.location.href = finalUrl + '.ipa';
			}
		}
		else {
			window.location.href = url;
		}
	}
}

///////////////////////////////////////
// for remove files, final warning
function deleteFiles(url, outboundDomain) {
	if (url) {
		if (outboundDomain && window.location.hostname === outboundDomain) {
			alert("[ WARNING ]\n\n 사내 네트워크에서만 삭제가 가능합니다.");
			return;
		}	
		var result = confirm("선택하신 빌드의 모든 파일이 삭제됩니다.\n\n삭제 후에는 수작업으로만 파일을 복구할 수 있습니다.\n\n\n완전 삭제를 진행 하시려면 '확인(OK)'를 누르세요.\n\n'취소(Cancel)'을 누르시면 임시 삭제(복원 가능)가 진행됩니다.\n\n");
		if (result == true) {
			window.location.href = url;
		}
	}
}

///////////////////////////////////////
// for iOS uDev3 only (Enterprise4Web)
function enterprise4web(url) {
	var result = confirm("[ Web Inspection 전용 버전 ]\n\n엔터프라이즈 개발자계정에 등록된 iPhone에서만 실행이 되는 버전입니다.\n\n그래도 받으시겠습니까?\n\n\n\n[ Web Inspection 방법 ]\n1. Enterprise4Web 버전을 다운로드 합니다. (등록되지 않은 폰은 설치가 다 되어도 icon이 dimmed 되어 실행되지 않음)\n2. iPhone > 설정 > Safari > 고급 > 'Javascript' 와 '웹 속성' 기능을 켭니다.\n3. Mac의 Safari.app 실행 > 환경 설정(Preferences) > 고급 (Advanced) > (하단) 개발자 기능을 메뉴바에 표시 (Show Develop menu in menu bar)\n﻿﻿4. Lightening cable로 Mac 에 iPhone을 연결합니다.\n5. Enterprise4Web 실행 후 > IoT 탭으로 이동\n6. Mac의 Safari.app 메뉴바 > 개발자 > (2번째 섹션) iPhone 이름란 > 'www.dev-some.co.kr' 과 같은 페이지 이름을 선택\n7. Safari.app의 Web Inspection 창에서 웹앱을 디버깅");
	if (result == true && url) {
		if (getMobileOperatingSystem() != "iOS" && url.startsWith("itms-services://")) {
			var plistFile = url.substring(46); // get-rid-of ``itms-services://?action=download-manifest&url=''
			var finalUrl = plistFile.split('.').slice(0,-1).join('.')
			window.location.href = finalUrl + '.ipa';
		}
		else {
			window.location.href = url;
		}
	}
}

///////////////////////////////////////
// Android signing for Android only
function androidSigning(url, file, apksignerVersion, unsignedGoogle, unsignedOneStore, outboundDomain) {
	if (outboundDomain && window.location.hostname === outboundDomain) {
		alert("[ Android 2차 난독화 ]\n\n 사내 네트워크에서만 가능합니다.");
		return;
	}
	var pathArray = window.location.pathname.split('/');
	var newPathname = "";
	// 상태 패스인 경우
	for (i = 0; i < pathArray.length - 1; i++) {
		newPathname += pathArray[i];
		newPathname += "/";
	}
	if (!unsignedGoogle) {
		const result = confirm("unsigned_" + file + "-GoogleStore-release.apk 파일이 업로드되지 않았습니다.\n\n업로드 사이트로 이동하시겠습니까?");
		if (result == true) {
			window.location.href = newPathname + "../phpmodules/upload.php?file=" + file;
		}
		return;
	}
	if (!unsignedOneStore) {
		const result = confirm("unsigned_" + file + "-OneStore-release.apk 파일이 업로드되지 않았습니다.\n\n업로드 사이트로 이동하시겠습니까?");
		if (result == true) {
			window.location.href = newPathname + "../phpmodules/upload.php?file=" + file;
		} else {
			if (apksignerVersion != '0') {
				const result = confirm("[ Android 2차 난독화 ]\n\n현재 apksigner v" + apksignerVersion + " 입니다.\n\n배포 버전과 맞는지 확인하시길 바랍니다.\n\n확인하셨다면 '확인(OK)'을 눌러서 진행합니다.");
				if (result == true && url) {
					window.location.href = url;
				}
			} else {
				alert("[ Android 2차 난독화 ]\n\napksigner 명령어가 없습니다.\n\n위치: " + url + "/*/apksigner");
			}
		}
		return;
	}
	if (apksignerVersion != '0') {
		const result = confirm("[ Android 2차 난독화 ]\n\n현재 apksigner v" + apksignerVersion + " 입니다.\n\n배포 버전과 맞는지 확인하시길 바랍니다.\n\n확인하셨다면 '확인(OK)'을 눌러서 진행합니다.");
		if (result == true && url) {
			window.location.href = url;
		}
	} else {
		alert("[ Android 2차 난독화 ]\n\napksigner 명령어가 없습니다.\n\n위치: " + url + "/*/apksigner");
	}
}

///////////////////////////////////////
// App Store uploading for iOS only
function appStoreUploading(url, appVersion, domesticEnd, outboundDomain) {
	if (outboundDomain && window.location.hostname === outboundDomain) {
		const result = confirm("[ App Store Upload ]\n\n 사내 네트워크에서만 가능합니다.\n\n사내 사이트로 이동하시려면 '확인(OK)'을 누르세요.");
		if (result == true && domesticEnd) {
			window.location.href = domesticEnd + window.location.pathname;
		}	
		return;
	}

	const result = confirm("[ App Store 업로드 ]\n\n엡스토어에 업로드 하실 버전은 v" + appVersion + " 입니다.\n\n용량에 따라 수십초에서 수분이 소요될 수 있으므로 페이지 이동하지 마시고 로딩이 완료될 때까지 기다려주십시오.\n\n확인하셨다면 '확인(OK)'을 눌러서 진행합니다.");
	if (result == true && url) {
		window.uploadingAnimation('loadingAni');
		window.location.href = url;
	}
}

function onClickSearchBox(targetInput) {

  // create invisible dummy input to receive the focus first
  const fakeInput = document.createElement('input')
  fakeInput.setAttribute('type', 'text')
  fakeInput.style.position = 'absolute'
  fakeInput.style.opacity = 0
  fakeInput.style.height = 0
  fakeInput.style.fontSize = '16px' // disable auto zoom

  // you may need to append to another element depending on the browser's auto
  // zoom/scroll behavior
  document.body.prepend(fakeInput)

  // focus so that subsequent async focus will work
  fakeInput.focus()

  setTimeout(() => {

    // now we can focus on the target input
    targetInput.focus()

    // cleanup
    fakeInput.remove()

  }, 1000)

}


function copyToClip(str) {
  function listener(e) {
    e.clipboardData.setData("text/html", str);
    e.clipboardData.setData("text/plain", str);
    e.preventDefault();
  }
  document.addEventListener("copy", listener);
  document.execCommand("copy");
  document.removeEventListener("copy", listener);
};

function logout() {
	document.location.replace('logout.php');
}