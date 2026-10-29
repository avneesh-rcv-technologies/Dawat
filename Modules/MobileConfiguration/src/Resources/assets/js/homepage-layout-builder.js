/**
 * Homepage Layout Builder Component
 * Handles drag-and-drop functionality and mobile preview
 */
class HomepageLayoutBuilder {
    constructor() {
        this.sectionIndex = 0;
        this.layoutSections = document.getElementById('layoutSections');
        this.emptyState = document.getElementById('emptyState');
        this.mobilePreview = document.getElementById('mobilePreview');
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updatePreview();
    }

    bindEvents() {
        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', () => {
                const slug = nameInput.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                slugInput.value = slug;
            });
        }

        // Add section buttons
        const addBannerBtn = document.getElementById('addBannerSection');
        const addFeatureBtn = document.getElementById('addFeatureSection');

        if (addBannerBtn) {
            addBannerBtn.addEventListener('click', () => this.addSection('banners'));
        }

        if (addFeatureBtn) {
            addFeatureBtn.addEventListener('click', () => this.addSection('features'));
        }
    }

    addSection(type) {
        const template = document.getElementById(type === 'banners' ? 'bannerSectionTemplate' : 'featureSectionTemplate');
        if (!template) return;

        const clone = template.content.cloneNode(true);
        
        // Replace INDEX with actual index
        const html = clone.querySelector('.section-item').outerHTML.replace(/INDEX/g, this.sectionIndex);
        
        const div = document.createElement('div');
        div.innerHTML = html;
        const sectionElement = div.firstElementChild;
        
        this.layoutSections.appendChild(sectionElement);
        
        // Hide empty state
        this.emptyState.style.display = 'none';
        
        // Add event listeners
        this.addSectionEventListeners(sectionElement);
        
        this.sectionIndex++;
        this.updatePreview();
    }

    addSectionEventListeners(section) {
        // Remove section
        const removeBtn = section.querySelector('.remove-section');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                section.remove();
                if (this.layoutSections.children.length === 1) { // Only empty state left
                    this.emptyState.style.display = 'block';
                }
                this.updatePreview();
            });
        }

        // Move up
        const moveUpBtn = section.querySelector('.move-up');
        if (moveUpBtn) {
            moveUpBtn.addEventListener('click', () => {
                const prev = section.previousElementSibling;
                if (prev && prev !== this.emptyState) {
                    this.layoutSections.insertBefore(section, prev);
                    this.updatePreview();
                }
            });
        }

        // Move down
        const moveDownBtn = section.querySelector('.move-down');
        if (moveDownBtn) {
            moveDownBtn.addEventListener('click', () => {
                const next = section.nextElementSibling;
                if (next) {
                    this.layoutSections.insertBefore(next, section);
                    this.updatePreview();
                }
            });
        }

        // Update preview on changes
        const inputs = section.querySelectorAll('select, input');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.updatePreview());
        });
    }

    updatePreview() {
        const sections = Array.from(this.layoutSections.querySelectorAll('.section-item'));
        let previewHtml = '';

        if (sections.length === 0) {
            previewHtml = `
                <div class="text-center text-gray-500 py-20">
                    <span class="material-icons-outlined text-4xl mb-2 block">smartphone</span>
                    <p>Preview will appear here</p>
                </div>
            `;
        } else {
            sections.forEach((section) => {
                const type = section.dataset.type;
                
                if (type === 'banners') {
                    const selectedBanners = Array.from(section.querySelectorAll('.banner-selector option:checked'));
                    const displayType = section.querySelector('select[name*="[display_type]"]')?.value || 'carousel';
                    
                    previewHtml += `
                        <div class="bg-gray-100 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <span class="material-icons-outlined text-blue-600 text-sm mr-1">image</span>
                                    <span class="text-sm font-medium">Banners (${displayType})</span>
                                </div>
                                <span class="text-xs text-gray-500">${selectedBanners.length} items</span>
                            </div>
                            <div class="grid ${displayType === 'carousel' ? 'grid-cols-1' : 'grid-cols-2'} gap-1">
                                ${selectedBanners.slice(0, displayType === 'carousel' ? 1 : 4).map(option => `
                                    <div class="bg-gray-300 rounded aspect-video flex items-center justify-center">
                                        <div class="text-center">
                                            <span class="material-icons-outlined text-gray-500 text-xs block">image</span>
                                            <span class="text-xs text-gray-600">${option.textContent.split('(')[0].trim()}</span>
                                        </div>
                                    </div>
                                `).join('')}
                                ${selectedBanners.length > (displayType === 'carousel' ? 1 : 4) ? 
                                    `<div class="bg-gray-200 rounded aspect-video flex items-center justify-center">
                                        <span class="text-xs text-gray-500">+${selectedBanners.length - (displayType === 'carousel' ? 1 : 4)} more</span>
                                    </div>` : ''
                                }
                            </div>
                        </div>
                    `;
                } else if (type === 'features') {
                    const selectedFeatures = Array.from(section.querySelectorAll('.feature-selector option:checked'));
                    const itemsPerRow = section.querySelector('select[name*="[items_per_row]"]')?.value || 2;
                    const displayType = section.querySelector('select[name*="[display_type]"]')?.value || 'grid';
                    
                    previewHtml += `
                        <div class="bg-gray-100 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <span class="material-icons-outlined text-green-600 text-sm mr-1">featured_play_list</span>
                                    <span class="text-sm font-medium">Features (${displayType})</span>
                                </div>
                                <span class="text-xs text-gray-500">${selectedFeatures.length} items</span>
                            </div>
                            <div class="grid grid-cols-${itemsPerRow} gap-2">
                                ${selectedFeatures.slice(0, 6).map(option => `
                                    <div class="bg-white rounded p-2 text-center">
                                        <div class="bg-gray-200 rounded aspect-square mb-1 flex items-center justify-center">
                                            <span class="material-icons-outlined text-gray-500 text-xs">category</span>
                                        </div>
                                        <div class="text-xs truncate">${option.textContent.split('(')[0].trim()}</div>
                                    </div>
                                `).join('')}
                                ${selectedFeatures.length > 6 ? 
                                    `<div class="bg-gray-200 rounded p-2 text-center flex items-center justify-center">
                                        <span class="text-xs text-gray-500">+${selectedFeatures.length - 6}</span>
                                    </div>` : ''
                                }
                            </div>
                        </div>
                    `;
                }
            });
        }

        this.mobilePreview.innerHTML = previewHtml;
    }

    // Load existing layout configuration
    loadExistingLayout(layoutConfig) {
        if (!layoutConfig || !Array.isArray(layoutConfig)) return;

        layoutConfig.forEach((section, index) => {
            this.addSection(section.type);
            
            // Set values for the added section
            const lastSection = this.layoutSections.lastElementChild;
            if (!lastSection) return;
            
            if (section.type === 'banners') {
                const bannerSelector = lastSection.querySelector('.banner-selector');
                const bannerIds = section.config?.banner_ids || [];
                
                bannerIds.forEach(bannerId => {
                    const option = bannerSelector?.querySelector(`option[value="${bannerId}"]`);
                    if (option) option.selected = true;
                });
                
                const displayType = lastSection.querySelector('select[name*="[display_type]"]');
                if (displayType) displayType.value = section.config?.display_type || 'carousel';
                
                const autoScroll = lastSection.querySelector('input[name*="[auto_scroll]"]');
                if (autoScroll) autoScroll.checked = section.config?.auto_scroll !== false;
                
                const showIndicators = lastSection.querySelector('input[name*="[show_indicators]"]');
                if (showIndicators) showIndicators.checked = section.config?.show_indicators !== false;
                
            } else if (section.type === 'features') {
                const featureSelector = lastSection.querySelector('.feature-selector');
                const featureIds = section.config?.feature_ids || [];
                
                featureIds.forEach(featureId => {
                    const option = featureSelector?.querySelector(`option[value="${featureId}"]`);
                    if (option) option.selected = true;
                });
                
                const displayType = lastSection.querySelector('select[name*="[display_type]"]');
                if (displayType) displayType.value = section.config?.display_type || 'grid';
                
                const itemsPerRow = lastSection.querySelector('select[name*="[items_per_row]"]');
                if (itemsPerRow) itemsPerRow.value = section.config?.items_per_row || 2;
                
                const showAllLink = lastSection.querySelector('input[name*="[show_all_link]"]');
                if (showAllLink) showAllLink.checked = section.config?.show_all_link === true;
            }
            
            this.addSectionEventListeners(lastSection);
        });
        
        this.updatePreview();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('layoutSections')) {
        window.layoutBuilder = new HomepageLayoutBuilder();
    }
});