import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

let lastValue = '';
let searchInput = '';

function createResultsList(projects) {
    const resultsContainer = document.getElementById('ucrisResults') || document.createElement('div');
    resultsContainer.id = 'ucrisResults';
    resultsContainer.innerHTML = '';
    resultsContainer.style.cssText = 'max-height: 300px; overflow-y: auto; border: 1px solid #ccc; margin-top: 10px;';

    projects.forEach(project => {
        const item = document.createElement('div');
        item.className = 'ucris-result-item';
        item.style.cssText = 'padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; hover:background-color: #f5f5f5;';
        item.textContent = `${project.name} (${project.id})`;
        item.setAttribute('data-id', project.id);
        item.setAttribute('data-name', project.name);
        item.addEventListener('click', () => findSelectAndAddOption(project.id, project.name));
        resultsContainer.appendChild(item);
    });

    const wizard = document.getElementById('ucrisproject');
    wizard.parentNode.insertBefore(resultsContainer, wizard.nextSibling);
}

function findSelectAndAddOption(dataId, dataName) {
    // Start from the ucrisResults element
    const ucrisResults = document.getElementById('ucrisResults');
    if (!ucrisResults) {
      console.error('Could not find element with ID "ucrisResults"');
      return false;
    }

    // Find the parent form-wizards-items-aside element
    let wizardsAside = ucrisResults.closest('.form-wizards-items-aside');
    if (!wizardsAside) {
      console.error('Could not find parent form-wizards-items-aside element');
      return false;
    }

    // Find the parent form-wizards-wrap element
    let wizardsWrap = wizardsAside.closest('.form-wizards-wrap');
    if (!wizardsWrap) {
      console.error('Could not find parent form-wizards-wrap element');
      return false;
    }

    // Find the form-wizards-element which contains the select
    let wizardsElement = wizardsWrap.querySelector('.form-wizards-element');
    if (!wizardsElement) {
      console.error('Could not find form-wizards-element');
      return false;
    }

    // Find the select element
    let selectElement = wizardsElement.querySelector('select');
    if (!selectElement) {
      console.error('Could not find select element');
      return false;
    }

    // Check if option with this dataId already exists
    const existingOption = Array.from(selectElement.options).find(option => option.value === dataId);
    if (existingOption) {
      console.log(`Option with data-id "${dataId}" already exists`);
      return true; // Option already exists, consider this a success
    }

    // Create and add the new option
    const option = new Option(dataName, dataId);
    option.setAttribute('selected', true);
    option.setAttribute('data-id', dataId);
    option.setAttribute('data-name', dataName);
    selectElement.add(option);

    // Trigger change event to ensure any listeners are notified
    const event = new Event('change', { bubbles: true });
    selectElement.dispatchEvent(event);

    console.log(`Added option with data-id "${dataId}" and data-name "${dataName}"`);
    return true;
  }

function getSearchString(){
   const searchInput = document.getElementById('search-project');
   const newValue = searchInput.value.trim();
   //const newValue='klettner';
    if (newValue === this.lastValue || newValue.length < 3){
      console.log('empty input');
      return;
    }
    this.lastValue = newValue;
    makeAjaxCall(newValue);
}

function makeAjaxCall(searchString){

    let request = new AjaxRequest(TYPO3.settings.ajaxUrls.univiepure_searchpureproject)
        .withQueryArguments({searchstring: searchString});

    let promise = request.get();
    promise.then(async function (response) {
        const projects = await response.resolve();
        createResultsList(projects);
    });
}

function getUcrisProject(){
    console.log('here i stand');
    const searchInput = document.getElementById('search-project') || document.createElement('input');
    searchInput.setAttribute('type', 'text');
    searchInput.id = 'search-project';
    const plusIcon = document.getElementById('ucrisproject');
    plusIcon.parentNode.insertBefore(searchInput, plusIcon.nextSibling);
    searchInput.addEventListener("input", getSearchString);
    searchInput.focus();

}

document.getElementById('ucrisproject').addEventListener("click", getUcrisProject);
