<script>
    // Cascading dropdowns for Country -> State -> City
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country_id');
            const stateSelect = document.getElementById('state_id');
            const citySelect = document.getElementById('city_id');

            if (countrySelect) {
                countrySelect.addEventListener('change', function() {
                    const countryId = this.value;
                    stateSelect.innerHTML = '<option value="">Loading...</option>';
                    citySelect.innerHTML = '<option value="">Select City</option>';

                    if (countryId) {
                        console.log('Loading states for country:', countryId);
                        
                        fetch(`/api/countries/states-by-country/${countryId}`)
                            .then(response => {
                                console.log('States API response status:', response.status);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('States API response data:', data);
                                stateSelect.innerHTML = '<option value="">Select State</option>';
                                
                                // Check if data has states array
                                const states = data.states || data;
                                
                                if (states && Array.isArray(states) && states.length > 0) {
                                    console.log(`Loading ${states.length} states`);
                                    states.forEach(state => {
                                        const option = document.createElement('option');
                                        option.value = state.id;
                                        option.textContent = state.name;
                                        stateSelect.appendChild(option);
                                    });
                                } else {
                                    console.warn('No states found in response');
                                    stateSelect.innerHTML = '<option value="">No states found</option>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading states:', error);
                                stateSelect.innerHTML = '<option value="">Error loading states</option>';
                            });
                    } else {
                        stateSelect.innerHTML = '<option value="">Select State</option>';
                    }
                });
            }

            if (stateSelect) {
                stateSelect.addEventListener('change', function() {
                    const stateId = this.value;
                    citySelect.innerHTML = '<option value="">Loading...</option>';

                    if (stateId) {
                        console.log('Loading cities for state:', stateId);
                        
                        fetch(`/api/countries/cities-by-state/${stateId}`)
                            .then(response => {
                                console.log('Cities API response status:', response.status);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Cities API response data:', data);
                                citySelect.innerHTML = '<option value="">Select City</option>';
                                
                                // Check if data has cities array
                                const cities = data.cities || data;
                                
                                if (cities && Array.isArray(cities) && cities.length > 0) {
                                    console.log(`Loading ${cities.length} cities`);
                                    cities.forEach(city => {
                                        const option = document.createElement('option');
                                        option.value = city.id;
                                        option.textContent = city.name;
                                        citySelect.appendChild(option);
                                    });
                                } else {
                                    console.warn('No cities found in response');
                                    citySelect.innerHTML = '<option value="">No cities found</option>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading cities:', error);
                                citySelect.innerHTML = '<option value="">Error loading cities</option>';
                            });
                    } else {
                        citySelect.innerHTML = '<option value="">Select City</option>';
                    }
                });
            }
        });
</script>

<!-- Google Maps Places API -->
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ fn_get_setting('general.google_maps_api_key') }}&libraries=places">
</script>

<!-- Google Places Autocomplete & Auto Geocoding Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addressForm = document.getElementById('addressForm');
        
        if (!addressForm) return;

        const addressLine1 = document.querySelector('input[name="address_line1"]');
        const addressLine2 = document.querySelector('input[name="address_line2"]');
        const postalCode = document.querySelector('input[name="postal_code"]');
        const latitudeInput = document.querySelector('input[name="latitude"]');
        const longitudeInput = document.querySelector('input[name="longitude"]');
        const countrySelect = document.getElementById('country_id');
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        const geoStatus = document.getElementById('geo-status');
        const mapLink = document.getElementById('geo-map-link');
        // Set when Places autocomplete fills the address, so the follow-up
        // dropdown changes don't overwrite its more precise coordinates.
        let autocompleteAt = 0;

        function setGeoStatus(message, tone) {
            if (!geoStatus) return;
            geoStatus.textContent = message || '';
            geoStatus.dataset.tone = tone || '';
            geoStatus.hidden = !message;
        }

        function updateMapLink() {
            if (!mapLink) return;
            const hasCoords = latitudeInput.value && longitudeInput.value;
            mapLink.hidden = !hasCoords;
            if (hasCoords) {
                mapLink.href = 'https://www.google.com/maps?q=' + encodeURIComponent(`${latitudeInput.value},${longitudeInput.value}`);
            }
        }

        function setCoordinates(lat, lng, message) {
            latitudeInput.value = Number(lat).toFixed(8);
            longitudeInput.value = Number(lng).toFixed(8);
            // Let the form's unsaved-changes tracking notice the update.
            latitudeInput.dispatchEvent(new Event('change', { bubbles: true }));
            latitudeInput.classList.add('border-green-500');
            longitudeInput.classList.add('border-green-500');
            setTimeout(() => {
                latitudeInput.classList.remove('border-green-500');
                longitudeInput.classList.remove('border-green-500');
            }, 2000);
            updateMapLink();
            setGeoStatus(message, 'success');
        }

        // Initialize Google Places Autocomplete
        if (typeof google !== 'undefined' && google.maps && google.maps.places && addressLine1) {
            const autocomplete = new google.maps.places.Autocomplete(addressLine1, {
                types: ['address'],
                fields: ['address_components', 'geometry', 'formatted_address']
            });

            // When user selects an address from autocomplete
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();

                if (!place.geometry) {
                    console.warn('No geometry found for selected place');
                    return;
                }

                // Extract address components
                const addressComponents = place.address_components;
                let streetNumber = '';
                let route = '';
                let locality = '';
                let adminArea1 = '';
                let country = '';
                let postalCodeValue = '';

                addressComponents.forEach(component => {
                    const types = component.types;
                    
                    if (types.includes('street_number')) {
                        streetNumber = component.long_name;
                    }
                    if (types.includes('route')) {
                        route = component.long_name;
                    }
                    if (types.includes('locality')) {
                        locality = component.long_name;
                    }
                    if (types.includes('administrative_area_level_1')) {
                        adminArea1 = component.long_name;
                    }
                    if (types.includes('country')) {
                        country = component.long_name;
                    }
                    if (types.includes('postal_code')) {
                        postalCodeValue = component.long_name;
                    }
                });

                // Update address line 1
                const fullStreetAddress = `${streetNumber} ${route}`.trim();
                if (fullStreetAddress) {
                    addressLine1.value = fullStreetAddress;
                }

                // Update postal code
                if (postalCodeValue && postalCode) {
                    postalCode.value = postalCodeValue;
                }

                // Update coordinates from the exact place the user picked.
                const location = place.geometry.location;
                autocompleteAt = Date.now();
                setCoordinates(location.lat(), location.lng(), 'Location set from the selected address.');

                // Try to match and select country, state, city from dropdowns
                matchAndSelectDropdowns(country, adminArea1, locality);

                console.log('Address autocompleted:', place.formatted_address);
            });
        }

        // Function to match and select values in dropdowns
        function matchAndSelectDropdowns(countryName, stateName, cityName) {
            // Match country
            if (countryName && countrySelect) {
                const countryOptions = Array.from(countrySelect.options);
                const matchedCountry = countryOptions.find(opt => 
                    opt.text.toLowerCase() === countryName.toLowerCase()
                );
                
                if (matchedCountry) {
                    countrySelect.value = matchedCountry.value;
                    countrySelect.dispatchEvent(new Event('change'));
                    
                    // Wait for states to load, then match state
                    setTimeout(() => {
                        if (stateName && stateSelect) {
                            const stateOptions = Array.from(stateSelect.options);
                            const matchedState = stateOptions.find(opt => 
                                opt.text.toLowerCase().includes(stateName.toLowerCase()) ||
                                stateName.toLowerCase().includes(opt.text.toLowerCase())
                            );
                            
                            if (matchedState) {
                                stateSelect.value = matchedState.value;
                                stateSelect.dispatchEvent(new Event('change'));
                                
                                // Wait for cities to load, then match city
                                setTimeout(() => {
                                    if (cityName && citySelect) {
                                        const cityOptions = Array.from(citySelect.options);
                                        const matchedCity = cityOptions.find(opt => 
                                            opt.text.toLowerCase() === cityName.toLowerCase()
                                        );
                                        
                                        if (matchedCity) {
                                            citySelect.value = matchedCity.value;
                                        }
                                    }
                                }, 500);
                            }
                        }
                    }, 500);
                }
            }
        }

        // Re-geocode whenever the address changes. The coordinate fields are
        // read-only, so they should always follow the address.
        let geocodeTimeout;
        function debounceGeocode() {
            clearTimeout(geocodeTimeout);
            geocodeTimeout = setTimeout(geocodeAddress, 800);
        }

        if (addressLine1) addressLine1.addEventListener('change', debounceGeocode);
        if (addressLine2) addressLine2.addEventListener('change', debounceGeocode);
        if (postalCode) postalCode.addEventListener('change', debounceGeocode);
        if (citySelect) citySelect.addEventListener('change', debounceGeocode);

        // Changing country or state clears the city, so wait for a city before geocoding.
        [countrySelect, stateSelect].forEach(function (select) {
            if (!select) return;
            select.addEventListener('change', function () {
                if (Date.now() - autocompleteAt < 4000) return;
                clearTimeout(geocodeTimeout);
                setGeoStatus('Select a city to update the map location.', 'warning');
            });
        });

        function geocodeAddress() {
            if (Date.now() - autocompleteAt < 4000) {
                return; // Autocomplete already supplied precise coordinates.
            }

            const selectedText = select => select && select.value && select.selectedOptions[0]
                ? select.selectedOptions[0].text.trim() : '';
            const addressParts = [
                addressLine1 && addressLine1.value.trim(),
                addressLine2 && addressLine2.value.trim(),
                selectedText(citySelect),
                selectedText(stateSelect),
                postalCode && postalCode.value.trim(),
                selectedText(countrySelect),
            ].filter(Boolean);
            const fullAddress = addressParts.join(', ');

            if (typeof google === 'undefined' || !google.maps || !google.maps.Geocoder) {
                setGeoStatus("Google Maps isn't available, so the location couldn't be updated.", 'error');
                return;
            }

            setGeoStatus('Finding location…', '');
            new google.maps.Geocoder().geocode({ address: fullAddress }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    const location = results[0].geometry.location;
                    setCoordinates(location.lat(), location.lng(), 'Location updated from the address.');
                } else {
                    console.warn('Geocoding failed:', status, fullAddress);
                    setGeoStatus("Couldn't find this address on the map. The previous coordinates were kept.", 'error');
                }
            });
        }

        updateMapLink();
    });
</script>
