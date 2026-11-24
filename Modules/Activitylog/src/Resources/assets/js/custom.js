(function () {
    const typeEl = document.getElementById('approval_type');
    const durationWrap = document.getElementById('allow-duration-wrap');
    const durationInput = document.getElementById('allow_duration');

    if (!typeEl || !durationWrap || !durationInput) {
        return;
    }

    const toggleDuration = () => {
        const isTemporary = typeEl.value === 'temporary';
        durationWrap.style.display = isTemporary ? 'block' : 'none';
        durationInput.required = isTemporary;
    };

    typeEl.addEventListener('change', toggleDuration);
    toggleDuration();
})();