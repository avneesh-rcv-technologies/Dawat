import AOS from 'aos';
import 'aos/dist/aos.css';

window.AOS = AOS;

AOS.init({
    duration: 700,
    easing: 'ease-out-cubic',
    offset: 60,
    once: true,
    mirror: false,
    disable: () => window.matchMedia('(prefers-reduced-motion: reduce)').matches,
});
