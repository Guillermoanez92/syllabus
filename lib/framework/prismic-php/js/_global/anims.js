import { gsap } from "gsap";
import { globalStorage, domStorage } from "./storage";
import {$scroll, $tilts} from "./_renderer";
import  EmblaCarousel  from "embla-carousel";
import {glob} from "glob";
import {LandingSlideshow} from "../_classes/LandingSlideshow";
import {Tilt} from "../_classes/Tilt";


/*
	Page specific animations
-------------------------------------------------- */
export const pageEntrance = (namespace = null)=> {

	/* ----- Establish our timeline ----- */
	let timeline = new gsap.timeline({ paused: true });

	/* ----- Setup cases for specific load-ins ----- */
	switch(namespace){
		/* ----- Our default page entrance ----- */
		default:
			let fadeUpEls = document.querySelectorAll(".fade-up");
			let fadeEls = document.querySelectorAll(".fade");
			timeline
				.fromTo(fadeUpEls, { autoAlpha: 0, y: 20 }, { y: 0, stagger: 0.06, autoAlpha: 1, ease: "sine.out", duration: 0.37 })
				.fromTo(fadeEls, { autoAlpha: 0 }, { stagger: 0.08, autoAlpha: 1, ease: "sine.inOut", duration: 0.45 });

			break;
	}
	gsap.set(domStorage.header, { scale: 0.97, autoAlpha: 0, force3D: true })

	gsap.to(domStorage.header, { y: 0, autoAlpha: 1, scale: 1, ease: "sine.inOut", duration: 0.45, force3D: true });

	timeline.to(domStorage.globalMask, 0.35, { autoAlpha: 0, ease: "sine.inOut", onComplete: ()=>{ globalStorage.transitionFinished = true; }}, 0.5);
	gsap.set(domStorage.clickMask, { pointerEvents: "none" });

	timeline.play();


	if (globalStorage.firstLoad) {
		globalStorage.firstLoad = false;
	}
};

/*
	Global element animations
-------------------------------------------------- */
export let $slideShow;
export const globalEntrance = ()=>{

	if(globalStorage.firstLoad !== true){
		return;
	}

	/* ----- Establish our timeline ----- */
	// let timeline = new gsap.timeline({ paused: true });
	// let logo = document.querySelector("#header .logo");

	// gsap.set(logo, { y: 26 });

	// timeline
	// 	.to("#header .logo", { duration: 0.4, y: 0, opacity: 1, ease: "sine.out", onComplete: ()=>{

	// 	}});
		globalStorage.transitionFinished = true;
	// timeline.play();
}


export const prepDrawers = () => {
	const drawers = document.querySelectorAll(".drawer:not(.bound)")

	for (let i = 0; i < drawers.length; i++) {
		const thisDrawer = drawers[i]
		let width = parseInt(thisDrawer.dataset.at);
		if (globalStorage.windowWidth > width) {
			continue;
		}
		thisDrawer.classList.add("bound")
		const childrenWrapper = thisDrawer.querySelector(".drawer-items")
		const childrenWrapperItems = childrenWrapper.querySelectorAll("p")
		const childrenWrapperHeight = childrenWrapper.offsetHeight

		thisDrawer.addEventListener("click", event => {
			if (!thisDrawer.classList.contains("open")) {
				const openDrawers = document.querySelectorAll(".drawer.open")
				for (let i = 0; i < openDrawers.length; i++) {
					openDrawers[i].classList.remove("open")
					gsap.to(openDrawers[i].querySelector(".drawer-items"), 0.35, { height: 0, force3D: true, ease: "sine.inOut" })
					gsap.to(openDrawers[i].querySelectorAll(".drawer-items p"), 0.35, { opacity: 0, force3D: true, ease: "sine.inOut" })
				}
				thisDrawer.classList.add("open")

				gsap.to(childrenWrapper, 0.35, { height: childrenWrapperHeight, force3D: true, ease: "sine.inOut", onComplete: () => {
						$scroll.resize()
					} })
				gsap.fromTo(childrenWrapperItems, 0.7, { opacity: .2 }, { opacity: 1, force3D: true, ease: "sine.inOut" })
			} else {
				thisDrawer.classList.remove("open")
				gsap.to(childrenWrapper, 0.35, { height: 0, force3D: true, ease: "sine.inOut", onComplete: () => {
						$scroll.resize()
					} })
				gsap.to(childrenWrapperItems, 0.35, { opacity: 0, force3D: true, ease: "sine.inOut" })
			}
		})
		gsap.set(childrenWrapper, { height: 0 })
		let origOffsetTop = thisDrawer.getBoundingClientRect().top - 120
		if ((i === drawers.length - 1) && $scroll) {
			$scroll.resize();
		}
	}
}

export class Marquees {

	constructor() {
		this.marquees = document.querySelectorAll('.marquee:not(.prepped)');

		this.init();
	}

	init() {
		this.getCache();
	}

	getCache(resize = false) {
		this.window2x = globalStorage.windowWidth * 2;

		for (let i = 0; i < this.marquees.length; i++) {
			const el = this.marquees[i];
			if (el.dataset.above) {
				let width = parseInt(el.dataset.above);

				if (globalStorage.windowWidth < width) {
					continue;
				}
			}
			let inner = this.marquees[i].querySelector('.inner'),
				copyEl = this.marquees[i].querySelector('[aria-hidden]'),
				copyWidth = copyEl.offsetWidth,
				multiplier,
				resetElCount = 2;

			if (copyWidth > globalStorage.windowWidth) {
				multiplier = 1;
			} else {
				multiplier = Math.ceil((globalStorage.windowWidth * 2) / copyWidth);
				resetElCount = Math.ceil(globalStorage.windowWidth / copyWidth);
			}

			let tween = this.prepMarkup(this.marquees[i], inner, multiplier, copyEl, resetElCount);

			globalStorage.marqueeData.push({
				el: this.marquees[i],
				tween: tween,
				playing: false
			});
		}
	}

	prepMarkup(marquee, inner, multiplier, copyEl, resetElCount) {
		for (let i = 1; i < multiplier; i++){
			let elementCopy = copyEl.cloneNode(true);
			elementCopy.classList.add("duplicate");
			inner.appendChild(elementCopy);
		}

		this.duplicates = inner.querySelectorAll(".duplicate");

		let resetDist = marquee.querySelector(".group:nth-child("+resetElCount+")").getBoundingClientRect().left;

		let dur = parseInt(inner.dataset.dur ? inner.dataset.dur : 25);

		let tween = gsap.fromTo(inner,{ x: 0 }, { duration: dur, repeat: -1, x: -resetDist, ease: "none", force3D: true }, 0).pause();

		marquee.classList.add('prepped');

		return tween;
	}


	resize() {
		for (let i = 0; i < this.duplicates.length; i++) {
			this.duplicates[i].remove();
		}

		this.getCache(true);
	}
}
export const prepSliders = () => {

	let prepControls = (slider, dots, prev, next) => {
		if (prev) {
			prev.addEventListener('click', () => {
				slider.scrollPrev();
			});
		}
		if (next) {
			next.addEventListener('click', () => {
				slider.scrollNext();
			});
		}
		if (dots) {
			for (let j = 0; j < dots.length; j++) {
				dots[j].addEventListener('click', () => {
					if (dots[j].classList.contains("active")) { return; }
					slider.scrollTo(j);
				});
			}
			slider.on("select", () => {
				dots[slider.previousScrollSnap()].classList.remove('active');
				dots[slider.selectedScrollSnap()].classList.add('active');
			});
		}

	};

	let sliders = document.querySelectorAll('.slider:not(.prepped)');
	for (let i = 0; i < sliders.length; i++) {
		const el = sliders[i];

		if (el.dataset.at) {
			let width = parseInt(el.dataset.at);

			if (globalStorage.windowWidth > width) {
				continue;
			}
		}

		const slideWrapper = el.querySelector('.slides');
		const prev = el.querySelector('.prev');
		const next = el.querySelector('.next');
		const dotsWrapper = el.querySelector('.dots');
		let dots = false;
		if (dotsWrapper) {
			dots = dotsWrapper.querySelectorAll('.dot');
		}


		const options = { loop: true, inViewThreshold: 0.3, startIndex: 0, align: 'start' };

		const slider = EmblaCarousel(slideWrapper, options);

		prepControls(slider, dots, prev, next);

		el.classList.add("prepped");
	}

};
export const prepModals = (modalTrigger) => {

	for (let i = 0; i < modalTrigger.length; i++) {
		const modalName = modalTrigger[i].dataset.modal;
		const modal = document.querySelector('.modal[data-modal="'+ modalName +'"]');
		const modalWrapper = modal.parentElement;
		const closeTrigger = modal.querySelector('.close-modal');
		const timeline = new gsap.timeline();

		gsap.set(modalWrapper, { autoAlpha: 0 })
		gsap.set(modal, { autoAlpha: 0 });

		gsap.set(closeTrigger, {autoAlpha: 0, scale: 0, })
		gsap.set(closeTrigger, {autoAlpha: 0, rotation: -360 })

		modalTrigger[i].addEventListener('click', () => {
			timeline.clear()
			timeline
				.to(modalWrapper, { autoAlpha: 1, ease: "sine.inOut", duration: 0.25, force3D: true })
				.to(modal, { autoAlpha: 1, ease: "sine.inOut", duration: 0.2, force3D: true }, 0.15)
				.to(closeTrigger, { autoAlpha: 1, scale: 1, ease: "expo.out", duration: 1, force3D: true }, globalStorage.windowWidth > 767 ? 0.43 : 0.25)
				.to(closeTrigger, { autoAlpha: 1, rotation: 0, ease: "expo.out", duration: 1.2, force3D: true }, globalStorage.windowWidth > 767 ? 0.48 : 0.3)
		});

		modal.addEventListener('click', (e) => {
			e.stopPropagation()
		});

		closeTrigger.addEventListener('click', () => {
			timeline.clear()
			timeline.progress(0)
			timeline
				.to(closeTrigger, { scale: 0, ease: "expo.out", duration: 1, force3D: true }, 0)
				.to(closeTrigger, { rotation: -360, ease: "expo.out", duration: 1, force3D: true }, 0)
				.to(modalWrapper, { autoAlpha: 0, ease: "sine.inOut", duration: 0.25, force3D: true }, 0.05)
				.to(modal, { autoAlpha: 0, ease: "sine.inOut", duration: 0.2, force3D: true }, 0.05);
		});

		modalWrapper.addEventListener('click', () => {
			timeline.clear()
			timeline.progress(0)
			timeline
				.to(closeTrigger, { scale: 0, ease: "expo.out", duration: 1, force3D: true }, 0)
				.to(closeTrigger, { rotation: -360, ease: "expo.out", duration: 1, force3D: true }, 0)
				.to(modalWrapper, { autoAlpha: 0, ease: "sine.inOut", duration: 0.25, force3D: true }, 0.05)
				.to(modal, { autoAlpha: 0, ease: "sine.inOut", duration: 0.2, force3D: true }, 0.05);
		});

		document.addEventListener('keyup', (event) => {
			let key = event.key;
			if (key === 'Escape' || key === 'Esc') {
				timeline.clear()
				timeline.progress(0)
				timeline
					.to(closeTrigger, { scale: 0, ease: "expo.out", duration: 1, force3D: true }, 0)
					.to(closeTrigger, { rotation: -360, ease: "expo.out", duration: 1, force3D: true }, 0)
					.to(modalWrapper, { autoAlpha: 0, ease: "sine.inOut", duration: 0.25, force3D: true }, 0.05)
					.to(modal, { autoAlpha: 0, ease: "sine.inOut", duration: 0.2, force3D: true }, 0.05);
			}
		});
	}

};
