import Highway from "@dogstudio/highway";
import { AddToCart } from "../_extensions/Shopify/AddToCart";

/*
    View Events for Highway

	- Products Page
    - Events are listed in their execution order
-------------------------------------------------- */
class ProductsRenderer extends Highway.Renderer{

	onEnter(){
		// console.log("onEnter");
	}

	onEnterCompleted(){
		// console.log("onEnterCompleted");

		// let forms = document.getElementsByClassName("add-to-cart-form");

		// Array.prototype.slice.call(forms).forEach((form)=>{

		// 	let addToCart = new AddToCart(form);

		// });
	}

	onLeave(){
		// console.log("onLeave");
	}

	onLeaveCompleted(){
		// console.log("onLeaveCompleted");
	}
}

export default ProductsRenderer;
