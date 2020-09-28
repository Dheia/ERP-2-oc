export default {
    update(el, binding) {
        let minusHeight = 0;

        binding.value.forEach(function (value) {
            minusHeight =  minusHeight + value;
        });

        el.style.height = window.innerHeight - (minusHeight) + "px";
        el.style.overflowY = 'scroll';
    }
}