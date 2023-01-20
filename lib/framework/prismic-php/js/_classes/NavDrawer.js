import { gsap } from "gsap";
import {domStorage, globalStorage} from "../_global/storage";
import {$scroll} from "../_global/_renderer";

export class NavDrawer {
    constructor() {
        this.nav = document.querySelector("nav");
        this.trigger = document.getElementById("nav-trigger");
        this.navDrawer = document.getElementById("nav-drawer");
        this.bgWhite = document.querySelector('.bg-white');
        this.isOpen = false;
        this.timeline = new gsap.timeline();
        this.bindListeners();
        console.log(this.nav);
    }

    bindListeners() {
            this.trigger.addEventListener("mouseenter", () => {
                if (this.isOpen) { return; }
                this.isOpen = true;
                if (this.isOpen) {
                    this.open();
                }

            });
            this.trigger.addEventListener("mousemove", (event) => {
                if (this.isOpen) { return; }
                this.isOpen = true;
                if (this.isOpen) {
                    this.open();
                }
            });
            this.trigger.addEventListener("mouseleave", () => {
                if (!this.isOpen) { return; }
                this.isOpen = false;
                this.close();
            });
    }

    open() {
        this.nav.classList.add('change-color');
        this.timeline.clear();

        this.timeline
            .set(this.navDrawer, { pointerEvents: "all" })
            .to(this.bgWhite, {duration: 0.2, autoAlpha: 1, force3D: true, ease: "sine.inOut"}, 0)
            .to(this.navDrawer, { duration: 0.22, autoAlpha: 1, force3D: true, ease: "sine.inOut" }, 0);
    }



    close() {
        this.nav.classList.remove('change-color');
        this.timeline.clear();
        this.timeline
            .set(this.navDrawer, { pointerEvents: "none" })
            .to(this.bgWhite, {duration: 0.2, autoAlpha: 0, force3D: true, ease: "sine.inOut"}, 0)
            .to(this.navDrawer, { duration: 0.2, autoAlpha: 0, force3D: true, ease: "sine.inOut" }, 0);
    }
}
