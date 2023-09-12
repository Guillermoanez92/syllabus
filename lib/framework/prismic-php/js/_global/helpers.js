import {domStorage, globalStorage} from "./storage";
import {$scroll, $tilts} from "./_renderer";
import { Tilt } from "../_classes/Tilt";
import {$slideShow, Marquees, prepSliders, prepDrawers, prepModals} from "./anims";
import { subscribe } from "klaviyo-subscribe";
import {LandingSlideshow} from "../_classes/LandingSlideshow";
import {gsap} from "gsap";
import {GlobalLazyLoadWorker} from "../_worker/GlobalLazyLoadWorker";
/*
 	Store any predefined global functions in here,
	useful for rewriting your favorite jquery function
	into a vanilla JS function.

-------------------------------------------------- */

/*
 	Get viewport
-------------------------------------------------- */
export const getViewport = function(){

	let e = window, a = "inner";

	if(!("innerWidth" in window)){
		a = "client";
		e = document.documentElement || document.body;
	}

	return { width: e[ a + "Width" ], height: e[ a + "Height" ] };
};

/*
 	Remove from array
-------------------------------------------------- */
export const remove = (array, key)=>{
	return array.filter(e => e !== key);
};

/*
	Analytics Helpers

-------------------------------------------------- */
/* global fbq analytics ga */
export const tracking = (type, var1 = null, var2 = null, var3 = null, var4 = null)=>{

	// console.log(type, var1, var2, var3);

	switch(type){
		case "facebook":

			if(typeof fbq === "undefined"){
				// Pixel not loaded
				console.log("FB not initialized");
			} else {

				if(var3){
					fbq(var1, var2, var3);
				} else if(var4) {
					fbq(var1, var2, var3, var4);
				} else {
					fbq(var1, var2);
				}
			}

			break;

		case "segment":

			if(typeof analytics === "undefined"){
				// Segment not loaded
				console.log("Segment not initialized");
			} else {

				switch(var1){
					case "track":

						// analytics.track(event, [properties], [options]);
						if(var4){
							analytics.track(var2, var3, var4);
						} else {
							analytics.track(var2, var3);
						}

						break;

					case "page":
						/* falls through */
					default:

						analytics.page();

						break;
				}
			}

			break;

		case "google":
			/* falls through */
		default:

			if(typeof ga === "undefined"){
				// GA not initialized
				console.log("GA not initialized");
			} else {

				if(var3){
					ga(var1, var2, var3);
				} else if(var4) {
					ga(var1, var2, var3, var4);
				} else {
					ga(var1, var2);
				}
			}

			break;
	}
};
const clearError = (elem)=>{

	let label = elem.previousElementSibling;

	if(!label || label === undefined){
		label = elem.nextElementSibling;
	}

	let placeholder = elem.getAttribute("placeholder");

	if(label.tagName.toLowerCase() == "label"){

		label.textContent = placeholder;
		label.classList.remove("error");
	}

	elem.parentElement.classList.remove("error");
};

const errorHandle = (elem, msg)=>{

	let label = elem.previousElementSibling;

	if(!label || label === undefined){
		label = elem.nextElementSibling;
	}

	if(label.tagName.toLowerCase() == "label"){
		label.textContent = msg;
		label.className += " error";
	}

	elem.parentElement.classList.add("error");
};
/*
 	Form Validation
-------------------------------------------------- */
const formValidation = (form)=>{

	let isValid = true;
	let passwordField = form.querySelector(".password");
	if (passwordField.value !== "") {
		return false;
	}
	let validate = form.querySelectorAll(".validate");


	validate.forEach((field)=>{

		// Clear Errors
		clearError(field);

		let type  = field.getAttribute("type");
		let tag   = field.tagName;
		let name  = field.getAttribute("name");
		let val   = field.value;

		// Account for Textarea
		if(tag == "textarea"){
			type = "textarea";
		}

		// Switch through Types
		switch(type){

			case "password":

				if(name == "customer[password_confirmation]"){

					let origElem = form.querySelectorAll("input[type='password']");
					let	origPass = origElem[0].value;

					// Check if Passwords Do Not Match
					if(origPass !== val){

						errorHandle(field, "Password's Do Not Match");

						isValid = false;
					}
				}

				// Check Password Length
				if(val.length < 5){

					errorHandle(field, "Your Password Must Be At Least 5 Characters");

					isValid = false;
				}

				break;

			case "text":
			case "textarea":

				if(val === ""){

					errorHandle(field, field.placeholder + " Can Not Be Blank");

					isValid = false;
				}

				break;

			case "email":

				let emailReg = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

				if(val === "" || !(val).match(emailReg)){
					errorHandle(field, "Please enter a valid email");

					isValid = false;
				}

				break;

			default:

				break;
		}
	});

	return isValid;
};


export const beforeScroll = () => {
	new Marquees();

	prepDrawers();
	prepModals(document.querySelectorAll(".modal-trigger"));
};

export const afterScroll = () => {
	prepSliders();
	bindKlaviyoForms();
	$scroll.checkScrollBasedLoadins(true);
	prepButtons();
	dogTabs();
};

export const loadGlobalScopeImages = (type) => {
	let images = document.querySelectorAll(".preload-global");

	let GlobalImageWorker = new GlobalLazyLoadWorker(window.worker);
	GlobalImageWorker.size = type
	GlobalImageWorker.loadImages(images, "nodeList", (returned)=>{
		// console.log("global scope images loaded");
	});
};

export const bindKlaviyoForms = () => {
	const forms = document.querySelectorAll(".bind-form:not(.bound)");
	for (let i = 0; i < forms.length; i++) {
		let form = forms[i];
		let showingError = false
		form.classList.add("bound");
		let submittedOnce = false;
		const emailField = form.querySelector('#email')
		const re = /\S+@\S+\.\S+/;
		form.addEventListener("submit", (event)=>{
			event.preventDefault();
			if (submittedOnce) { return; }
			const listId = form.dataset.id;
			const email = emailField.value;
			if (!re.test(email)) {
				showingError = true
				emailField.classList.add('error')
				return;
			}
			submittedOnce = true;
			gsap.to(form, { opacity: 0, duration: 0.3, ease: "sine.inOut", onComplete: () => {
					// form.removeChild(form.querySelector("#email"));
					// form.removeChild(form.querySelector("button"));
					gsap.delayedCall(0.5, () => {
						gsap.to(form.nextElementSibling, { autoAlpha: 1, duration: 0.3, ease: "sine.inOut" });
					})
				}
			});
			subscribe(listId, email, {
				// any optional traits
			}).then((response) => {
				console.log(response)
			});
		});
		if (globalStorage.isMobile) {
			emailField.addEventListener('change', () => {
				if (!showingError) {
					return
				}
				if (emailField.value.length > 0) {
					if (re.test(emailField.value)) {
						showingError = false
						emailField.classList.remove('error')
					}
				}
			} )
		} else {
			emailField.addEventListener('keyup', () => {
				if (!showingError) {
					return
				}
				if (emailField.value.length > 0) {
					if (re.test(emailField.value)) {
						showingError = false
						emailField.classList.remove('error')
					}
				}
			} )
		}
	}
};

/* SYllABUS */

const prepButtons = () => {
	let buttons = document.querySelectorAll(".function-btn");
	const buttonCoords = [];

	for (let i = 0; i < buttons.length; i++) {
		const btn = buttons[i];
		const origPos = btn.getBoundingClientRect();

		const coords = {
			x: 0,
			y: 0,
			active: false,
			origPos: {
				top: origPos.top,
				bottom: origPos.bottom,
				height: origPos.height
			}
		};


		buttonCoords.push(coords);

		btn.addEventListener("click", () => {
			coords.active = true;

		});
	}

};


const dogTabs = () => {
	const dogTiles = document.querySelectorAll(".new-prep-tabs .tile");
	const buttons = document.querySelectorAll(".new-prep-tabs button");

	for (let i = 0; i < dogTiles.length; i++) {

		let el = dogTiles[i];

		for (let j = 0; j < buttons.length; j++) {
			let button = buttons[j],
				associatedTiles = button.dataset.dog === 'all' ? dogTiles : el.dataset.dog;
				button.addEventListener("click", event => {
					gsap.to(dogTiles, { display: 'none' });
					gsap.to(associatedTiles, { autoAlpha: 0, ease: "sine.inOut", duration: 0.35, force3D: true });
					gsap.to(associatedTiles, { display: 'flex' });
				});
		}


	}
};
