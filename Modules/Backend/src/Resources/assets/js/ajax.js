(function () {
  "use strict";
  document.addEventListener('DOMContentLoaded', function () {
    const notifications = [];

    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabLinks.forEach(l => {
                l.classList.remove('active', 'border-blue-600', 'text-blue-600');
                l.classList.add('border-transparent', 'text-gray-600');
            });
            
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active', 'border-blue-600', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-600');
            
            // Show target content
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.remove('hidden');
                targetContent.classList.add('active');
            }
        });
    });
    // Loader Management Functions
    const Loader = {
      // Create loader element with blur background
      createLoader: function () {
        const loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.className = 'fixed inset-0 backdrop-blur-sm z-50 flex items-center justify-center';

        // Semi-transparent overlay
        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 bg-white-100 bg-opacity-20';
        loader.appendChild(overlay);

        // Centered rounded box (smaller size)
        const loaderBox = document.createElement('div');
        loaderBox.className = 'relative bg-blue-100 bg-opacity-90 rounded-xl p-3 shadow-xl flex flex-col items-center justify-center';
        loaderBox.style.minWidth = '80px';
        loaderBox.style.minHeight = '80px';

        // Circular loader animation (smooth and modern)
        const spinner = document.createElement('div');
        spinner.className = 'animate-spin rounded-full h-10 w-10 border-4 border-solid border-t-blue-500 border-r-blue-500 border-b-transparent border-l-transparent';

        // // Optional loading text
        // const loadingText = document.createElement('div');
        // loadingText.className = 'mt-3 text-gray-600 text-sm font-medium';
        // loadingText.textContent = 'Loading...';

        loaderBox.appendChild(spinner);
        // loaderBox.appendChild(loadingText);
        loader.appendChild(loaderBox);
        document.body.appendChild(loader);

        return loader;
      },

      // Show loader
      show: function () {
        let loader = document.getElementById('ajax-loader');
        if (!loader) {
          loader = this.createLoader();
        }
        loader.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
      },

      // Hide loader
      hide: function () {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
          loader.style.display = 'none';
          document.body.style.overflow = ''; // Restore scrolling
          // Optional: Remove loader from DOM after hiding
          setTimeout(() => {
            if (loader.parentNode) {
              loader.parentNode.removeChild(loader);
            }
          }, 300);
        }
      }
    };

    // Add the required CSS dynamically
    const loaderCSS = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      .animate-spin {
        animation: spin 0.8s linear infinite;
      }
      #ajax-loader {
        display: none;
      }
      .backdrop-blur-sm {
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
      }
    `;
    const style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(loaderCSS));
    document.head.appendChild(style);
    // Initialize all existing forms
    initializeAjaxForms();
    initRichTextEditors();

    // Set up MutationObserver to handle dynamically added forms
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.addedNodes.length) {
          initializeAjaxForms();
          initRichTextEditors();
          initStateFormScript();

          mutation.addedNodes.forEach(function (node) {
            if (node.nodeType === 1) {
              initImageFilePreviews(node);
            }
          });
        }
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
    initImageFilePreviews();


    const GAP = 8;
    let openMenu = null;

    function closeMenu() {
      if (openMenu) {
        openMenu.style.display = "none";
        openMenu.classList.add("opacity-0", "scale-95", "invisible");
        openMenu = null;
      }
    }

    function openMenuAt(trigger, menu) {
      closeMenu();

      // make measurable
      menu.style.display = "block";
      menu.style.position = "fixed";
      menu.style.visibility = "hidden";
      menu.classList.remove("opacity-0", "scale-95", "invisible");

      const triggerRect = trigger.getBoundingClientRect();
      const menuRect = menu.getBoundingClientRect();
      const vw = window.innerWidth;
      const vh = window.innerHeight;

      let top = triggerRect.bottom + GAP;
      let left = triggerRect.right - menuRect.width;

      // flip up if not enough space below
      if (vh - triggerRect.bottom < menuRect.height + GAP) {
        top = triggerRect.top - menuRect.height - GAP;
      }

      // keep inside viewport horizontally
      if (left < 8) left = 8;
      if (left + menuRect.width > vw - 8) left = vw - menuRect.width - 8;

      // apply final position
      menu.style.top = `${top}px`;
      menu.style.left = `${left}px`;
      menu.style.visibility = "visible";

      menu.classList.add("opacity-100", "scale-100", "visible");
      openMenu = menu;
    }

    document.addEventListener("click", function (e) {
      const trigger = e.target.closest(".dropdown-trigger");
      if (trigger) {
        e.preventDefault();
        const id = trigger.getAttribute("data-id");
        const menu = document.querySelector(`.dropdown-menu[data-id="${id}"]`);
        if (!menu) return;

        if (menu === openMenu) {
          closeMenu();
        } else {
          openMenuAt(trigger, menu);
        }
      } else if (!e.target.closest(".dropdown-menu")) {
        closeMenu();
      }
    });
    // end dropdown

    /**
     * Initialize rich text editors for all textareas with editor="true" attribute.
     * Supports the globally selected editor so modules can keep using the same markup.
     */
    function initRichTextEditors() {
      const editorType = window.appEditorType || 'ckeditor';

      document.querySelectorAll('textarea[editor="true"]').forEach(textarea => {
        if (textarea.dataset.editorInitialized === 'true' || textarea.dataset.editorInitializing === 'true') return;

        if (!textarea.id) {
          textarea.id = `editor-${Math.random().toString(36).slice(2, 11)}`;
        }

        if (editorType === 'tinymce') {
          initTinyMceEditor(textarea);
          return;
        }

        if (editorType === 'ckeditor') {
          initCkEditor(textarea);
        }
      });
    }

    function initTinyMceEditor(textarea) {
      if (typeof tinymce === 'undefined') return;

      const editorId = textarea.id;
      const heightEditor = parseInt(textarea.dataset.height || '280', 10);

      if (tinymce.get(editorId)) {
        textarea.dataset.editorInitialized = 'true';
        return;
      }

      textarea.dataset.editorInitializing = 'true';

      tinymce.init({
        selector: `#${editorId}`,
        plugins: 'autolink code table lists link wordcount',
        toolbar: 'undo redo | bold italic | bullist numlist | link | alignleft aligncenter alignright alignjustify',
        menubar: false,
        statusbar: false,
        height: heightEditor,
        setup: function (editor) {
          editor.on('init', function () {
            delete textarea.dataset.editorInitializing;
            textarea.dataset.editorInitialized = 'true';
            editor.setContent(textarea.value || '');
          });

          editor.on('change input undo redo', function () {
            textarea.value = editor.getContent();
          });
        }
      });
    }

    function initCkEditor(textarea) {
      if (typeof ClassicEditor === 'undefined') return;

      const minHeight = parseInt(textarea.dataset.height || '280', 10);

      textarea.dataset.editorInitializing = 'true';

      ClassicEditor
        .create(textarea, {
          toolbar: [
            'undo', 'redo',
            '|',
            'heading',
            '|',
            'bold', 'italic', 'link',
            '|',
            'bulletedList', 'numberedList',
            '|',
            'blockQuote', 'insertTable'
          ]
        })
        .then(editor => {
          delete textarea.dataset.editorInitializing;
          textarea.dataset.editorInitialized = 'true';
          textarea.ckeditorInstance = editor;

          const editable = editor.ui.view.editable.element;
          if (editable) {
            editable.style.minHeight = `${minHeight}px`;
          }

          editor.model.document.on('change:data', function () {
            textarea.value = editor.getData();
          });
        })
        .catch(error => {
          delete textarea.dataset.editorInitializing;
          console.error('CKEditor initialization failed:', error);
        });
    }

    function initializeAjaxForms() {
      document.querySelectorAll('.form-ajax').forEach(form => {
        // Skip forms that already have event listeners
        if (form.hasAttribute('data-ajax-initialized')) return;

        form.setAttribute('data-ajax-initialized', 'true');

        form.addEventListener('submit', function (e) {
          e.preventDefault();
          handleFormSubmission(this);
        });
      });
    }

    function initStateFormScript() {
      const form = document.getElementById('stateForm');
      if (!form) return; // if form not present, skip

      const countrySelect = form.querySelector('#country');
      const countryCodeInput = form.querySelector('#country_code');
      if (!countrySelect || !countryCodeInput) return;

      function setCountryCodeFromSelected() {
        const selectedOption = countrySelect.options[countrySelect.selectedIndex];
        const code = selectedOption.getAttribute('data-code') || '';
        countryCodeInput.value = code;
      }

      // bind only once (prevent duplicate binding if popup reopens)
      if (!countrySelect.dataset.bound) {
        countrySelect.addEventListener('change', setCountryCodeFromSelected);
        countrySelect.dataset.bound = "true";
      }

      if (!countryCodeInput.value) {
        setCountryCodeFromSelected();
      }
    }

    function handleFormSubmission(form) {
      // Find the submit button (more reliable than form.button)
      const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
      let btnText = submitButton ? submitButton.textContent : 'Submit';

      removeErrorDivs(form); // Remove existing errors

      const action = form.getAttribute('action');
      const method = form.getAttribute('method') || 'POST'; // Default to POST

      if (!action) {
        displayError(form, 'The form action property is not set!');
        return;
      }

      Loader.show();

      // Disable submit button and show loading state
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';
      }

      const formData = new FormData(form);
      formData.append('is_ajax', '1');

      // Set up headers
      const headers = new Headers();
      headers.append('X-Requested-With', 'XMLHttpRequest');
      headers.append('Accept', 'application/json');

      // Add CSRF token from multiple possible sources
      const csrfToken = form.querySelector('input[name="_token"]')?.value ||
        form.querySelector('input[name="csrf_token"]')?.value ||
        document.querySelector('meta[name="csrf-token"]')?.content;

      if (csrfToken) {
        headers.append('X-CSRF-TOKEN', csrfToken);
      }

      // Make the AJAX call
      call(form, action, method, formData, btnText, headers, submitButton);
    }
    function initImageFilePreviews(context = document) {
      const fileInputs = context.querySelectorAll('input[type="file"].preview-image-input');

      fileInputs.forEach(function (input) {
        if (!input.dataset.previewHandlerAttached) {
          input.dataset.previewHandlerAttached = "true";

          input.addEventListener("change", function () {
            const previewId = this.dataset.previewTarget; // Optional: custom preview container id
            let previewEl;

            if (previewId) {
              previewEl = document.getElementById(previewId);
            } else {
              // Create preview container if not exists
              if (!this.nextElementSibling || !this.nextElementSibling.classList.contains("image-preview")) {
                previewEl = document.createElement("div");
                previewEl.className = "image-preview mt-2";
                this.insertAdjacentElement("afterend", previewEl);
              } else {
                previewEl = this.nextElementSibling;
              }
            }

            previewEl.innerHTML = "";

            if (this.files && this.files[0] && this.files[0].type.startsWith("image/")) {
              const img = document.createElement("img");
              img.src = URL.createObjectURL(this.files[0]);
              img.className = "h-20 w-20 object-cover rounded ";
              previewEl.appendChild(img);
            }
          });
        }
      });
    }



    function call(thisForm, action, method, formData, btnText, headers) {
      console.log(thisForm);

      fetch(action, {
        method: method,
        body: formData,
        headers: headers
      })
        .then(response => {
          Loader.hide();
          if (response.status === 401) {
            return response.json().then(result => {
              if (result.redirect_url) {
                window.location.replace(result.redirect_url);
                return null;
              }
              throw new Error('Your session has expired. Please sign in again.');
            });
          }
          if (response.status === 419) {
            throw new Error('Your session has expired. Please refresh the page and try again.');
          }
          // Access denials and validation errors contain messages for the user.
          if (response.ok || response.status === 403 || response.status === 422 || response.status === 429) {
            return response.text();
          } else {
            throw new Error(`${response.status} ${response.statusText} ${response.url}`);
          }
        })
        .then(data => {
          if (data === null) return;
          const submitButton = thisForm.querySelector('button[type="submit"], input[type="submit"]');
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = btnText;
          }

          var res = JSON.parse(data);

          if (res.errors) {
            errorCreate(res.errors, thisForm);
          } else if (res.success === false) {
            displayError(thisForm, res.message || 'Unable to complete your request. Please try again.', btnText);
          }

          if (res.success) {
            showToast(res.message, 'success', 'Success');
            sessionStorage.setItem('toastMessage', JSON.stringify({
              message: res.message,
              type: 'success',
              title: 'Success'
            }));
          }

          if (res.callback) {
            // res.callback;
            if (res.callback && typeof res.callback === 'string') {
              new Function(res.callback)(); // runs "loadOrder()"
            }
          }

          if (res.redirect_url) {
            window.location.href = res.redirect_url;
          }


        })
        .catch((error) => {
          displayError(thisForm, error, btnText);
        });
    }

    function displayError(thisForm, error, btnText) {
      Loader.hide();
      const message = error instanceof Error ? error.message : error;
      showToast(message, 'error', 'Error');
      const submitButton = thisForm.querySelector('button[type="submit"], input[type="submit"]');
      if (submitButton) {
        submitButton.removeAttribute('disabled');
        if (btnText !== undefined) submitButton.textContent = btnText;
      }
    }

    function errorCreate(errors, formData) {
      console.log(errors);
      if (errors.length === undefined) {
        $.each(errors, function (i, v) {
          var errorText = `<div class="text-red-500 text-sm mt-1" id="errors"><small>${v}</small></div>`;
          var id = `#${i}`;
          showToast(v, 'error', 'Error');
          if ($(formData).find(id).length) {
            let $field = $(formData).find(id);
            $(formData).find(id).addClass('!border !border-red-500');
            $(formData).find(id).parent().append(errorText);
            let $label =
              $field.closest('div').find('label').first();

            if ($label.length) {
              $label.addClass('!text-red-500');
            }
          }
        });
      } else {
        showToast(errors, 'error', 'Error');
      }
    }

    function removeErrorDivs(formData) {
      var errorElements = formData.querySelectorAll('#errors');
      errorElements.forEach(function (errorElement) {
        $(errorElement).parent().find('input, select, textarea').removeClass('!border !border-red-500');
        errorElement.remove();
      });
    }


    // Modified ceAjax function with loader and CSRF token support
    function ceAjax(method, url, options) {
      // Default options
      const config = {
        result_ids: '',
        caching: true,
        callback: null,
        errorCallback: null,
        beforeSend: null,
        complete: null,
        data: null,
        headers: {},
        loader: false, // Default to no loader
        ...options
      };

      // Show loader if enabled
      if (config.loader) {
        Loader.show();
      }

      // Execute beforeSend callback if provided
      if (typeof config.beforeSend === 'function') {
        config.beforeSend();
      }

      // Create XMLHttpRequest
      const xhr = new XMLHttpRequest();

      // Handle caching
      const finalUrl = config.caching ? url : `${url}${url.includes('?') ? '&' : '?'}_=${Date.now()}`;

      // Determine HTTP method
      const httpMethod = method === 'request' ? 'GET' : method.toUpperCase();

      xhr.open(httpMethod, finalUrl, true);

      // Set headers
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      // Add CSRF token for non-GET requests
      if (httpMethod !== 'GET') {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
          xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }
      }

      // Add custom headers
      for (const [key, value] of Object.entries(config.headers)) {
        xhr.setRequestHeader(key, value);
      }

      xhr.onload = function () {
        // Hide loader if it was shown
        if (config.loader) {
          Loader.hide();
        }

        // Execute complete callback if provided
        if (typeof config.complete === 'function') {
          config.complete();
        }

        if (xhr.status >= 200 && xhr.status < 300) {
          let response;
          try {
            response = JSON.parse(xhr.responseText);
          } catch (e) {
            response = xhr.responseText;
          }

          // Automatic DOM updates based on result_ids
          if (config.result_ids) {
            const resultIds = typeof config.result_ids === 'string'
              ? config.result_ids.split(',')
              : Object.keys(config.result_ids);

            resultIds.forEach(id => {
              const element = document.getElementById(id.trim());
              if (element) {
                if (typeof response === 'object' && response[id.trim()]) {
                  element.innerHTML = response[id.trim()];
                } else if (typeof response === 'string') {
                  element.innerHTML = response;
                }
              }
            });
          }

          // Execute success callback if provided
          if (typeof config.callback === 'function') {
            config.callback(response, config.data);
          }
        } else if (xhr.status === 401) {
          let response;
          try {
            response = JSON.parse(xhr.responseText);
          } catch (e) {
            response = {};
          }
          if (response.redirect_url) {
            window.location.replace(response.redirect_url);
          } else if (typeof config.errorCallback === 'function') {
            config.errorCallback(xhr);
          }
        } else if (xhr.status === 422) {
          // Validation errors - still parse JSON and pass to callback so callers can handle
          let response;
          try {
            response = JSON.parse(xhr.responseText);
          } catch (e) {
            response = xhr.responseText;
          }

          if (typeof config.callback === 'function') {
            config.callback(response, config.data);
          }
        } else {
          console.error('Request failed:', xhr.statusText);
          if (typeof config.errorCallback === 'function') {
            config.errorCallback(xhr);
          }
        }
      };

      xhr.onerror = function () {
        // Hide loader on error too
        if (config.loader) {
          Loader.hide();
        }

        // Execute complete callback if provided
        if (typeof config.complete === 'function') {
          config.complete();
        }

        console.error('Network error occurred');
        if (typeof config.errorCallback === 'function') {
          config.errorCallback(xhr);
        }
      };

      // Prepare data
      let requestData = null;
      if (config.data) {
        if (httpMethod === 'GET') {
          const params = new URLSearchParams(config.data).toString();
          xhr.open(httpMethod, `${finalUrl}${finalUrl.includes('?') ? '&' : '?'}${params}`, true);
        } else {
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          requestData = new URLSearchParams(config.data).toString();
        }
      }

      // Send request
      xhr.send(requestData);
    }

    // Make it available globally
    window.ceAjax = ceAjax;

  });

  // Tags manager
  const tagContainer = document.getElementById('tag-container');
  const tagsInput = document.getElementById('tags-input');
  const hiddenInput = document.getElementById('tags');
  const datalist = document.getElementById('tagList');

  // Check if required elements exist
  if (!tagContainer || !tagsInput || !hiddenInput || !datalist) {
    console.error('One or more required elements for tag manager not found');
    return;
  }

  // Initialize with existing tags
  const initialTags = hiddenInput.value ? hiddenInput.value.split(',').filter(tag => tag.trim()) : [];
  initialTags.forEach(tag => addTag(tag.trim()));
  updateHiddenInput();

  tagsInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleTagInput();
    } else if (e.key === ',' || e.key === ';') {
      e.preventDefault();
      handleTagInput();
    } else if (e.key === 'Backspace' && this.value === '') {
      const tags = tagContainer.querySelectorAll('.tag');
      if (tags.length > 0) {
        const lastTag = tags[tags.length - 1];
        removeTag(lastTag);
        tagsInput.focus();
      }
    }
  });

  tagsInput.addEventListener('blur', function () {
    if (this.value.trim() !== '') {
      handleTagInput();
    }
  });

  function handleTagInput() {
    if (tagsInput.value.trim() !== '') {
      addTag(tagsInput.value.trim());
      tagsInput.value = '';
    }
  }

  function addTag(tagName) {
    if (!tagName) return;

    // Check if tag already exists
    const existingTags = Array.from(tagContainer.querySelectorAll('.tag')).map(tag => tag.dataset.tag);
    if (existingTags.includes(tagName)) {
      tagsInput.value = '';
      return;
    }

    const tagElement = document.createElement('div');
    tagElement.className = 'tag inline-flex items-center bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm';
    tagElement.dataset.tag = tagName;

    tagElement.innerHTML = `
            ${tagName}
            <button type="button" class="ml-1.5 -mr-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 focus:outline-none">
                &times;
            </button>
        `;

    tagElement.querySelector('button').addEventListener('click', function () {
      removeTag(tagElement);
      tagsInput.focus();
    });

    // Insert before the input
    tagContainer.insertBefore(tagElement, tagsInput);
    updateHiddenInput();
  }

  function removeTag(tagElement) {
    if (tagElement && tagElement.parentNode) {
      tagElement.remove();
      updateHiddenInput();
    }
  }

  function updateHiddenInput() {
    if (!hiddenInput) return;
    const tags = Array.from(tagContainer.querySelectorAll('.tag')).map(tag => tag.dataset.tag);
    hiddenInput.value = tags.join(',');
  }

})();
