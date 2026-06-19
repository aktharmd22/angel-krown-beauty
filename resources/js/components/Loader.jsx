import { useEffect, useState } from 'react';

export default function Loader() {
    const [done, setDone] = useState(false);
    const [gone, setGone] = useState(false);

    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce) {
            setGone(true);
            return;
        }
        const t1 = setTimeout(() => setDone(true), 1500);
        const t2 = setTimeout(() => setGone(true), 2600);
        return () => {
            clearTimeout(t1);
            clearTimeout(t2);
        };
    }, []);

    if (gone) return null;

    return (
        <div className={done ? 'loader done' : 'loader'} aria-hidden="true">
            <div className="loader-inner">
                <img src="/assets/img/logo.png" alt="Angel Krown" className="loader-logo" />
                <div className="loader-bar"><i /></div>
            </div>
        </div>
    );
}
