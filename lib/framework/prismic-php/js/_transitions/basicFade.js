import Highway from "@dogstudio/highway";
import { gsap } from "gsap";
import { globalStorage } from "../_global/storage";

let globalMask = document.getElementById("global-mask");

/*
	Default Highway Transition
-------------------------------------------------- */
class BasicFade extends Highway.Transition{

	out({from, trigger, done}){

		gsap.fromTo(globalMask, 0.3, { autoAlpha: 0 }, { autoAlpha: 1, ease: "sine.out", onComplete: () => { done() } });
	}

	in({from, to, trigger, done}){

		globalStorage.namespace = to.dataset.routerView;

		// Move to top of page
		window.scrollTo(0, 0);

		// Remove old view
		from.remove();
		globalStorage.transitionFinished = true;

		done();
	}
}

export default BasicFade;
