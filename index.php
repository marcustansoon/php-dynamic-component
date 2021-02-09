<?php
require "Interactive/Classes/ContactForm.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Page Title</title>
	</head>
	<body>
		<?= (new ContactForm)->renderHTML() ?>
	</body>
	<script src="https://cdn.jsdelivr.net/npm/reefjs@8/dist/reef.min.js"></script>
	<script type="module">
		let interactiveDataElements = document.querySelectorAll('[interactive-data]'),
		dynamicClasses =  document.querySelectorAll('[d-class]');

		dynamicClasses.forEach(function(elem){
			let renderHTML,
			isRendering,
			shouldFocusElement,
			app = new Reef(elem, {
	    		template: function () {
	    			return renderHTML;
	    		},
	    	}),
			bindedNodes = [],
			responseText,
			focusedElement;

			// Listen for when Reef components are rendered into the DOM
			elem.addEventListener('reef:render', function (event) {
				console.log('rendered')
				shouldFocusElement = shouldFocusElement && focusedElement && focusedElement.focus()
			});

			function sendPOSTrequest(){
				// Remove non-existence nodes
				bindedNodes = bindedNodes.filter(node => node.isConnected)
				// Get model bindings
				let bindedValues = bindedNodes.reduce((nodeValues, node) => {
					nodeValues[node.getAttribute('d-bind-model')] = node.value
					return nodeValues
				}, {})

				fetch('Interactive/Requests/Request.php', {
		    		method: 'post',
		    		body: JSON.stringify({
		    			'class': elem.getAttribute('d-class'),
		    			'variables': bindedValues,
		    		})
		  		})
				// Debug
				//.then(response => response.text())
				/*.then(data => {
					renderHTML = data
					app.render()
					elem.querySelectorAll('[d-bind-model]').forEach(innerElem => {
						// Check if node is binded. If so, exit
						if(bindedNodes.find(node => node.isSameNode(innerElem))) return;
						// Bind 'change' listener
						bindedNodes.push(innerElem)
						innerElem.addEventListener('change', sendPOSTrequest)
					})
				})*/
				// Deployment
				.then(response => response.text())
				.then(text => {
					responseText = text
					return JSON.parse(text)
				})
				.then(json => {
					//console.log(json)
					renderHTML = json.html
					// Keep track of rendering process
					isRendering = true
					// Initiate rendering (dom-diff)
					app.render()
					isRendering = false
					// Add event listeners
					elem.querySelectorAll('[d-bind-model]').forEach(innerElem => {
						innerElem.value = json.variables[innerElem.getAttribute('d-bind-model')]
						console.log(json.variables[innerElem.getAttribute('d-bind-model')])
						// Check if node is binded. If so, exit
						if(bindedNodes.find(node => node.isSameNode(innerElem))) return;
						// Bind 'change' listener
						bindedNodes.push(innerElem)
						console.log('binded')
						innerElem.addEventListener('input', sendPOSTrequest)
						// Fix auto focus out bug
						innerElem.addEventListener('focus', () => focusedElement = innerElem)
						innerElem.addEventListener('focusout', () =>{
							// Check if element should be focused after rendering
							shouldFocusElement = focusedElement && isRendering ? true : false;
							focusedElement = shouldFocusElement ? focusedElement : null;
						})
					})
				})
				.catch(error => {
					document.body.innerHTML += responseText;
  					console.error('Error:', responseText)
				})
			}
			// Init
			sendPOSTrequest()
		})
	</script>
</html>