// For example: <script
// src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">
let autocomplete;
let address1Field;
let address2Field;
let postalField;
let fieldsCity;

function initAutocomplete() {
  address1Field = document.querySelector("#fields_address1");
  address2Field = document.querySelector("#fields_address2");
  postalField = document.querySelector("#fields_zip");
  fieldsCity = document.querySelector("#fields_city");
  const countrySelect = document.querySelector("#fields_country_select");
  
  autocomplete = new google.maps.places.Autocomplete(address1Field, {
    componentRestrictions: { country: ["us"] },
    fields: ["address_components", "geometry"],
    types: ["address"]
  });
  
  countrySelect.addEventListener("change", () => {
    const selectedCountry = countrySelect.value.toLowerCase();
    if (selectedCountry === "us") {
        document.querySelector(".pac-container").classList.remove("hide-autocomplete");  
    } else {    
        document.querySelector(".pac-container").classList.add("hide-autocomplete"); 
    }
  });
  autocomplete.addListener("place_changed", fillInAddress);
}

function fillInAddress() {
  // Get the place details from the autocomplete object.
  const place = autocomplete.getPlace();
  let address1 = "";
  let postcode = "";
  let country ="";
  let state ="";

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  // place.address_components are google.maps.GeocoderAddressComponent objects
  // which are documented at http://goo.gle/3l5i5Mr
  for (let component of place.address_components) {
    // @ts-ignore remove once typings fixed
    let componentType = component.types[0];

    switch (componentType) {
      case "street_number": {
        address1 = `${component.long_name} ${address1}`;
        break;
      }

      case "route": {
        address1 += component.short_name;
        break;
      }

      case "postal_code": {
        postcode = `${component.long_name}${postcode}`;
        break;
      }

      case "postal_code_suffix": {
        postcode = `${postcode}-${component.long_name}`;
        break;
      }
      case "locality":
        fieldsCity.value = component.long_name;
        if(component.long_name!=""){
           fieldsCity.classList.remove("error")
           const citylabel= fieldsCity.nextElementSibling;
           if (citylabel) {
                citylabel.setAttribute("style", "font-size: 0.8rem; top: 5px;");            
           }
        }
        break;
      
      case "country":
        country = component.short_name;
        
        break;
      case "administrative_area_level_1": {
        state=component.short_name;        
        break;
      }
    }
  }

  
  //country
  let select_country = document.querySelector("#fields_country_select");
  select_country.value = country;

  if(document.getElementById("shipping-container")==null){
     select_country.dispatchEvent(new Event("change", { bubbles: true }));
  }  

  //state
  var select_state = document.querySelector("#fields_state");
  select_state.classList.remove("error")    
  if (document.querySelectorAll("#fields_state option[value='"+state+"']").length > 0) {    
      select_state.value = state;
      select_state.dispatchEvent(new Event("change", { bubbles: true }));
  }else{
      var select = document.getElementById("fields_state");
      var found=false;
      for (var option of select.options) {    
          if (option.text.toLowerCase().includes(state.toLowerCase())) {
              found=true;
              option.selected = true;
              break;
          }
      }

      if(!found){
        var option = new Option(state, state, false, true);
        select_state.add(option);
      }
  }  

  address1Field.value = address1;
  if(postcode!=""){
        postalField.classList.remove("error")
        const ziplabel= postalField.nextElementSibling;
        if (ziplabel) {
            ziplabel.setAttribute("style", "font-size: 0.8rem; top: 5px;");
        }
        postalField.value = postcode;
   }

  var address_1_el=document.querySelector('#fields_address1');
  var address_1_=document.querySelector('#fields_address1 + span.error-message');
  if(address_1_ && !address_1_el.classList.contains('error')){
    address_1_.remove();
  }

  var city_el=document.querySelector('#fields_city');
  var city_=document.querySelector('#fields_city + span.error-message');
  if(city_ && !city_el.classList.contains('error')){
    city_.remove();
  }
  
  var state_el=document.querySelector('#fields_state');
  var state_=document.querySelector('#fields_state + span.error-message');
  if(state_ && !state_el.classList.contains('error')){
    state_.remove();
  }

  var zip_el=document.querySelector('#fields_zip');
  var zip_=document.querySelector('#fields_zip + span.error-message');
  if(zip_ && !zip_el.classList.contains('error')){
    zip_.remove();
  }

  // After filling the form with address components from the Autocomplete
  // prediction, set cursor focus on the second address line to encourage
  // entry of subpremise information such as apartment, unit, or floor number.
  address2Field.focus();
}

window.initAutocomplete = initAutocomplete;