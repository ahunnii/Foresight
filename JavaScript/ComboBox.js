/** 
* Set as the onChange attribute's value for a select 
* element to update a corresponding input element.
*
* @param pList The select element that fired the event
* @param pInput Id of the input element to change
*/
function onOptionSelected(pList, pInput) {
	pInput = document.getElementById(pInput);
	var index = pList.selectedIndex;
	var content = pList.options[index].value;
	pInput.value = content;
}