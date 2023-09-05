/**
 * @file
 * CKEditor Image Credidential plugin. Based on core link plugin and inspired from cke-entity-link module.
 *
 * @ignore
 */

(function ($, Drupal, drupalSettings, CKEDITOR) {
	'use strict';
  
	CKEDITOR.plugins.add('image_credidentials', {
	  init: function (editor) {
		// Add the commands for link and unlink.
		editor.addCommand('image_credidentials', {
		  allowedContent: new CKEDITOR.style({
			element: 'p',
			styles: {},
			attributes: {
			  '!credit': '',
			}
		  }),
		  modes: {wysiwyg: 1},
		  exec: function (editor) {
			var linkElement = getSelectedLink(editor);
			var linkDOMElement = null;
  
			// Set existing values based on selected element.
			var existingValues = {};
			if (linkElement && linkElement.$) {
			  linkDOMElement = linkElement.$;
  
			  // Populate an array with the link's current attributes.
			  var attribute = null;
			  var attributeName;
			  for (var attrIndex = 0; attrIndex < linkDOMElement.attributes.length; attrIndex++) {
				attribute = linkDOMElement.attributes.item(attrIndex);
				attributeName = attribute.nodeName.toLowerCase();

				if (attributeName.substring(0, 15) === 'data-cke-saved-') {
				  continue;
				}
				existingValues[attributeName] = linkElement.data('cke-saved-' + attributeName) || attribute.nodeValue;
			  }

			}
  
			// Prepare a save callback to be used upon saving the dialog.
			var saveCallback = function (returnValues) {
			  editor.fire('saveSnapshot');
  
			  // Create a new link element
			  if (!linkElement && returnValues.attributes.credit) {
			
				var selection = editor.getSelection();
				var range = selection.getRanges(1)[0];

				// Use Returned values to generate HTML code
				if (range.collapsed) {
					
					// Set Text Elements from Returned values or not
					var creditTag = editor.document.createElement('p', editor.document);
					var creditText = document.createTextNode( 'crédit: ' + returnValues.attributes.credit );
					var creditContent = new CKEDITOR.dom.text( creditText );
					creditTag.setAttribute('class', 'credit');

					// Apply elements created to CKE DOM and append children
					range.insertNode(creditContent);
					range.insertNode(creditTag);
					creditTag.append(creditContent);

					// Insert Contents to Wyswyg
					range.selectNodeContents(creditContent);
				}
  
				// Create the new link by applying a style to the new text.
				var style = new CKEDITOR.style({element: 'p', attributes: returnValues.attributes.credit});
				style.type = CKEDITOR.STYLE_INLINE;
				style.applyToRange(range);
				range.select();

			  }
			  // Update the link properties.
			  else if (linkElement) {
				for (var attrName in returnValues.attributes) {
				  if (returnValues.attributes.hasOwnProperty(attrName)) {
					// Update the property if a value is specified.
					if (returnValues.attributes[attrName].length > 0) {
					  var value = returnValues.attributes[attrName];
					  linkElement.data('cke-saved-' + attrName, value);
					  linkElement.setAttribute(attrName, value);
					}
					// Delete the property if set to an empty string.
					else {
					  linkElement.removeAttribute(attrName);
					}
				  }
				}
			  }
  
			  // Save snapshot for undo support.
			  editor.fire('saveSnapshot');
			};
			// Drupal.t() will not work inside CKEditor plugins because CKEditor
			// loads the JavaScript file instead of Drupal. Pull translated
			// strings from the plugin settings that are translated server-side.
			var dialogSettings = {
			  title: linkElement ? editor.config.image_credidentials_dialogTitleEdit : editor.config.image_credidentials_dialogTitleAdd,
			  dialogClass: 'editor-image-credidentials-popup-dialog-base'
			};

			// Open the dialog for the edit form.
			Drupal.ckeditor.openDialog(editor, Drupal.url('icdc_mediatheque/dialog/' + editor.config.drupal.format), existingValues, saveCallback, dialogSettings);
		  }
		});
  
		// Add buttons for link and unlink.
		if (editor.ui.addButton) {
		  editor.ui.addButton('imageCredidentials', {
			label: Drupal.t("Insérer un crédit d'image"),
			command: 'image_credidentials',
			icon: this.path + '/icons/credidential.png',
			toolbar: 'insert,10'
		  });
		}
	  }
	});
  
	/**
	 * Get the surrounding link element of current selection.
	 *
	 * The following selection will all return the link element.
	 *
	 * @example
	 *  <a href="#">li^nk</a>
	 *  <a href="#">[link]</a>
	 *  text[<a href="#">link]</a>
	 *  <a href="#">li[nk</a>]
	 *  [<b><a href="#">li]nk</a></b>]
	 *  [<a href="#"><b>li]nk</b></a>
	 *
	 * @param {CKEDITOR.editor} editor
	 *   The CKEditor editor object
	 *
	 * @return {?HTMLElement}
	 *   The selected element, or null.
	 *
	 */
	function getSelectedLink(editor) {
	  var selection = editor.getSelection();
	  var selectedElement = selection.getSelectedElement();

	  if (selectedElement && selectedElement.is('button')) {
		return selectedElement;
	  }
  
	  var range = selection.getRanges(true)[0];
  
	  if (range) {
		range.shrink(CKEDITOR.SHRINK_TEXT);
		return editor.elementPath(range.getCommonAncestor()).contains('button', 1);
	  }
	  return null;
	}
  
  })(jQuery, Drupal, drupalSettings, CKEDITOR);
  