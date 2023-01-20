/*
	Global Storage Object
-------------------------------------------------- */
export const globalStorage = {
	"assetPath": (document.getElementById("site-data") && window.location.host.indexOf("localhost") < 0) ? document.getElementById("site-data").getAttribute("data-asset-path") : "/assets/code/",
	"firstLoad": true,
	"isMobile": false,
	"isSafari": (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) ? true : false,
	"isChrome": (navigator.userAgent.indexOf("Chrome") > -1) ? true : false,
	"isFirefox": (navigator.userAgent.indexOf("Firefox") > -1) ? true : false,
	"windowHeight": "",
	"windowWidth": "",
	"transitionFinished": false,
	"queryParams": {},
	"referrer": "",
	"reducedMotion": window.matchMedia("(prefers-reduced-motion: reduce)").matches,
	"headerShowing": true,
	"marqueeData": [],
};

export const domStorage = {
	"header": document.getElementById("header"),
	"mainEl": document.getElementById("main"),
	"containerEl": document.getElementById("container"),
	"globalMask": document.getElementById("global-mask"),
	"clickMask": document.getElementById("click-mask"),
	"pencilBar": document.getElementById("pencil-bar"),
	"sceneWrapper": "",
	"scenes": "",
	"sceneOneFrames": "",
	"sceneTwoFrames": "",
	"sceneThreeFrames": "",
	"openMobileMenu": ()=>{},
	"closeMobileMenu": ()=>{},
	"resetMobileMenu": ()=>{}
}
