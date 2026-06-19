import { Suspense, useRef, useEffect } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { Sparkles, Float } from '@react-three/drei';
import * as THREE from 'three';

function Ring({ radius, tube, color, emissive, speed, tilt, pointer }) {
    const ref = useRef();
    useFrame((state, dt) => {
        if (!ref.current) return;
        ref.current.rotation.z += dt * speed;
        ref.current.rotation.x = THREE.MathUtils.lerp(ref.current.rotation.x, tilt + pointer.current.y * 0.25, 0.04);
        ref.current.rotation.y = THREE.MathUtils.lerp(ref.current.rotation.y, pointer.current.x * 0.4, 0.04);
    });
    return (
        <mesh ref={ref}>
            <torusGeometry args={[radius, tube, 28, 240]} />
            <meshStandardMaterial
                color={color}
                emissive={emissive}
                emissiveIntensity={0.6}
                metalness={0.35}
                roughness={0.35}
            />
        </mesh>
    );
}

function Scene({ pointer }) {
    return (
        <Float speed={1.1} rotationIntensity={0.15} floatIntensity={0.5}>
            <group>
                <Ring radius={2.85} tube={0.022} color="#8B1A4F" emissive="#8B1A4F" speed={0.12} tilt={0.5} pointer={pointer} />
                <Ring radius={2.25} tube={0.014} color="#C9A24B" emissive="#C9A24B" speed={-0.18} tilt={-0.35} pointer={pointer} />
                <Ring radius={3.35} tube={0.008} color="#F2A6C4" emissive="#F2A6C4" speed={0.08} tilt={0.9} pointer={pointer} />
            </group>
        </Float>
    );
}

export default function HeroScene({ sparkleCount = 140 }) {
    const pointer = useRef({ x: 0, y: 0 });

    useEffect(() => {
        const onMove = (e) => {
            pointer.current.x = (e.clientX / window.innerWidth - 0.5) * 2;
            pointer.current.y = (e.clientY / window.innerHeight - 0.5) * 2;
        };
        window.addEventListener('pointermove', onMove, { passive: true });
        return () => window.removeEventListener('pointermove', onMove);
    }, []);

    return (
        <Canvas
            dpr={[1, 1.8]}
            camera={{ position: [0, 0, 6], fov: 45 }}
            gl={{ antialias: true, alpha: true }}
            style={{ position: 'absolute', inset: 0, pointerEvents: 'none', touchAction: 'pan-y' }}
        >
            <ambientLight intensity={0.9} />
            <directionalLight position={[3, 4, 5]} intensity={1.8} color="#ffd9a8" />
            <pointLight position={[-4, -2, 3]} intensity={18} color="#F2A6C4" />
            <Suspense fallback={null}>
                <Scene pointer={pointer} />
                <Sparkles count={sparkleCount} scale={[14, 9, 5]} size={2.4} speed={0.3} opacity={0.5} color="#C9A24B" />
            </Suspense>
        </Canvas>
    );
}
