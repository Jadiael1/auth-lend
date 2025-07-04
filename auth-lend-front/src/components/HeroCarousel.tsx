"use client";

import Image from "next/image";
import { useEffect, useState, useCallback, useMemo } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

// import BpcLoasAndLegalRepresentative from "@/components/images/bpc_loas_and_legal_representative.png";
// import Fgts from "@/components/images/fgts.png";
// import PublicServant from "@/components/images/public_servant.png";
// import RetireesAndPensioners from "@/components/images/retirees_and_pensioners.jpg";
// import WorkerCredit from "@/components/images/worker_credit.png";

interface Slide {
    title: string;
    subtitle: string;
    description: string;
    subDescription: string;
    image: string;
}

const slides: Slide[] = [
    {
        title: "Facilitamos Seu Empréstimo",
        subtitle:
            "Condições especiais para aposentados e pensionistas com aprovação rápida e segura.",
        description:
            "Refinanciamento, portabilidade com troco e cartão consignado.",
        subDescription: "Confira nossas opções",
        image: "https://picsum.photos/id/1018/1000/600",
        // image: RetireesAndPensioners.src,
    },
    {
        title: "Empréstimo Facilitado para BPC/LOAS e Representante Legal",
        subtitle: "",
        description:
            "Oferecemos crédito rápido e seguro para beneficiários do BPC/LOAS e seus representantes legais.",
        subDescription:
            "Refinanciamento, portabilidade com troco e cartão consignado.",
        image: "https://picsum.photos/id/1015/1000/600",
        // image: BpcLoasAndLegalRepresentative.src,
    },
    {
        title: "Antecipe seu FGTS Saque-Aniversário",
        subtitle:
            "Garanta agora o valor do seu FGTS Saque-Aniversário com facilidade e segurança.",
        description: "Receba em até 20 minutos.",
        subDescription: "*Sujeito a análise de crédito e condições do produto.",
        image: "https://picsum.photos/id/1016/1000/600",
        // image: Fgts.src,
    },
    {
        title: "Empréstimo novinho e em folha",
        subtitle: "",
        description: "Crédito do Trabalhador: Empréstimo Consignado para CLT.",
        subDescription:
            "Conheça a nova modalidade de empréstimo exclusiva para trabalhadores com carteira assinada e com melhores taxas comparadas a outros empréstimos. Aproveite!",
        image: "https://picsum.photos/id/1021/1000/600",
        // image: PublicServant.src,
    },
    {
        title: "Crédito Exclusivo para Servidores Públicos",
        subtitle: "",
        description:
            "Condições especiais de empréstimo para servidores estaduais, municipais e federais, com aprovação rápida e segura.",
        subDescription: "",
        image: "https://picsum.photos/id/1020/1000/600",
        // image: WorkerCredit.src,
    },
];

export default function HeroCarousel() {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [position, setPosition] = useState(1);
    const [isTransitioning, setIsTransitioning] = useState(true);
    const [isNavigating, setIsNavigating] = useState(false);
    const [touchStart, setTouchStart] = useState(0);
    const SWIPE_THRESHOLD = 50;

    const handlePointerDown = (e: React.PointerEvent) => {
        const targetElement = e.target as HTMLElement;
        if (targetElement.closest('[data-no-swipe="true"]')) {
            return;
        }
        setTouchStart(e.clientX);
    };

    const handlePointerUp = (e: React.PointerEvent) => {
        if (touchStart === 0) return;
        const touchEnd = e.clientX;
        const delta = touchEnd - touchStart;
        if (delta > SWIPE_THRESHOLD) {
            prev();
        } else if (delta < -SWIPE_THRESHOLD) {
            next();
        }
        setTouchStart(0);
    };

    const slidesWithClones = useMemo(() => {
        return [slides[slides.length - 1], ...slides, slides[0]];
    }, []);

    const prev = () => {
        if (isNavigating || !isTransitioning) return;
        setIsNavigating(true);
        setPosition((prev) => prev - 1);
    };

    const next = useCallback(() => {
        if (isNavigating || !isTransitioning) return;
        setIsNavigating(true);
        setPosition((prev) => prev + 1);
    }, [isNavigating, isTransitioning]);

    const handleTransitionEnd = () => {
        setIsNavigating(false);
        if (position === 0) {
            setIsTransitioning(false);
            setPosition(slides.length);
        } else if (position === slidesWithClones.length - 1) {
            setIsTransitioning(false);
            setPosition(1);
        }
    };

    useEffect(() => {
        if (!isTransitioning) {
            const timer = setTimeout(() => setIsTransitioning(true), 50);
            return () => clearTimeout(timer);
        }
    }, [isTransitioning]);

    useEffect(() => {
        const timer = setInterval(next, 8000);
        return () => clearInterval(timer);
    }, [next]);

    useEffect(() => {
        if (position === 0) {
            setCurrentIndex(slides.length - 1);
        } else if (position === slides.length + 1) {
            setCurrentIndex(0);
        } else {
            setCurrentIndex(position - 1);
        }
    }, [position]);

    return (
        <section className="relative w-full h-80 md:h-[32rem] overflow-hidden">
            <div
                className="w-full h-full flex cursor-grab active:cursor-grabbing select-none"
                style={{
                    transform: `translateX(-${position * 100}%)`,
                    transition: isTransitioning
                        ? "transform 0.7s ease-in-out"
                        : "none",
                }}
                onTransitionEnd={handleTransitionEnd}
                onPointerDown={handlePointerDown}
                onPointerUp={handlePointerUp}
            >
                {slidesWithClones.map((slide: Slide, index: number) => (
                    <div
                        key={index}
                        className="w-full h-full flex-shrink-0 relative"
                    >
                        <Image
                            src={slide.image}
                            alt={slide.title}
                            fill
                            priority={index === 1}
                            sizes="100vw"
                            className="object-cover md:object-right"
                        />
                        <div className="hidden md:block absolute inset-0 bg-gradient-to-l from-transparent via-white/70 to-white dark:via-gray-900/70 dark:to-gray-900" />
                        <div className="hidden md:flex absolute inset-y-0 left-0 w-1/2 flex-col justify-center gap-4 p-22 items-start">
                            <h2
                                data-no-swipe="true"
                                className="text-3xl font-bold cursor-auto select-text"
                            >
                                {slide.title}
                            </h2>
                            <h3
                                data-no-swipe="true"
                                className="text-xl font-semibold text-gray-600 dark:text-gray-300 cursor-auto select-text"
                            >
                                {slide.subtitle}
                            </h3>
                            <p
                                data-no-swipe="true"
                                className="text-gray-700 dark:text-gray-200 cursor-auto select-text"
                            >
                                {slide.description}
                            </p>
                            <p
                                data-no-swipe="true"
                                className="text-gray-700 dark:text-gray-200 cursor-auto select-text"
                            >
                                {slide.subDescription}
                            </p>
                            <button
                                data-no-swipe="true"
                                className="w-max px-4 py-2 bg-blue-600 text-white rounded-md cursor-pointer"
                            >
                                Saiba mais
                            </button>
                        </div>
                        <div className="md:hidden absolute inset-0 bg-black/50 flex flex-col justify-end p-18 gap-2 text-white">
                            <h2 className="text-2xl font-bold">
                                {slide.title}
                            </h2>
                            <h3 className="text-lg font-semibold">
                                {slide.subtitle}
                            </h3>
                            <p>{slide.description}</p>
                            <p>{slide.subDescription}</p>
                            <button className="mt-2 w-max px-4 py-2 bg-blue-600 text-white rounded-md">
                                Saiba mais
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="absolute inset-0 flex items-center justify-between z-10 pointer-events-none">
                <button
                    onClick={prev}
                    disabled={isNavigating}
                    aria-label="Previous slide"
                    className="ml-4 p-2 bg-white/70 rounded-full shadow-md hover:bg-white cursor-pointer pointer-events-auto transition-opacity disabled:opacity-50"
                >
                    <ChevronLeft className="w-5 h-5 text-gray-800" />
                </button>
                <button
                    onClick={next}
                    disabled={isNavigating}
                    aria-label="Next slide"
                    className="mr-4 p-2 bg-white/70 rounded-full shadow-md hover:bg-white cursor-pointer pointer-events-auto transition-opacity disabled:opacity-50"
                >
                    <ChevronRight className="w-5 h-5 text-gray-800" />
                </button>
            </div>
            <div className="absolute bottom-5 left-0 right-0 flex justify-center gap-2 z-10">
                {slides.map((_, i) => (
                    <button
                        key={i}
                        onClick={() => {
                            if (isNavigating) return;
                            setIsNavigating(true);
                            setPosition(i + 1);
                        }}
                        aria-label={`Go to slide ${i + 1}`}
                        className={`h-2 rounded-full transition-all duration-300 ease-in-out ${
                            currentIndex === i
                                ? "w-8 bg-white"
                                : "w-2 bg-white/60"
                        }`}
                    />
                ))}
            </div>
        </section>
    );
}
