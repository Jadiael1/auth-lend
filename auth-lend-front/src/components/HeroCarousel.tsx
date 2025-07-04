"use client";

import Image, { StaticImageData } from "next/image";
import { useEffect, useState, useCallback, useMemo } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useSwipeable } from "react-swipeable";

import BpcLoasAndLegalRepresentative from "@/components/images/bpc_loas_and_legal_representative.png";
import Fgts from "@/components/images/fgts.png";
import PublicServant from "@/components/images/public_servant.png";
import RetireesAndPensioners from "@/components/images/retirees_and_pensioners.png";
import WorkerCredit from "@/components/images/worker_credit.png";

interface Slide {
    title: string;
    subtitle: string;
    description: string;
    subDescription: string;
    image: string | StaticImageData;
}

const slides: Slide[] = [
    {
        title: "Facilitamos Seu Empréstimo",
        subtitle:
            "Condições especiais para aposentados e pensionistas com aprovação rápida e segura.",
        description:
            "Refinanciamento, portabilidade com troco e cartão consignado.",
        subDescription: "Confira nossas opções",
        // image: "https://picsum.photos/id/1018/1000/600",
        image: RetireesAndPensioners,
    },
    {
        title: "Empréstimo Facilitado para BPC/LOAS e Representante Legal",
        subtitle: "",
        description:
            "Oferecemos crédito rápido e seguro para beneficiários do BPC/LOAS e seus representantes legais.",
        subDescription:
            "Refinanciamento, portabilidade com troco e cartão consignado.",
        // image: "https://picsum.photos/id/1015/1000/600",
        image: BpcLoasAndLegalRepresentative,
    },
    {
        title: "Antecipe seu FGTS Saque-Aniversário",
        subtitle:
            "Garanta agora o valor do seu FGTS Saque-Aniversário com facilidade e segurança.",
        description: "Receba em até 20 minutos.",
        subDescription: "*Sujeito a análise de crédito e condições do produto.",
        // image: "https://picsum.photos/id/1016/1000/600",
        image: Fgts,
    },
    {
        title: "Empréstimo novinho e em folha",
        subtitle: "",
        description: "Crédito do Trabalhador: Empréstimo Consignado para CLT.",
        subDescription:
            "Conheça a nova modalidade de empréstimo exclusiva para trabalhadores com carteira assinada e com melhores taxas comparadas a outros empréstimos. Aproveite!",
        // image: "https://picsum.photos/id/1021/1000/600",
        image: PublicServant,
    },
    {
        title: "Crédito Exclusivo para Servidores Públicos",
        subtitle: "",
        description:
            "Condições especiais de empréstimo para servidores estaduais, municipais e federais, com aprovação rápida e segura.",
        subDescription: "",
        // image: "https://picsum.photos/id/1020/1000/600",
        image: WorkerCredit,
    },
];

export default function HeroCarousel() {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [position, setPosition] = useState(1);
    const [isTransitioning, setIsTransitioning] = useState(true);
    const [isNavigating, setIsNavigating] = useState(false);

    const handlers = useSwipeable({
        onSwipedLeft: () => next(),
        onSwipedRight: () => prev(),
        trackMouse: true,
    });

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
        <section className="relative w-full min-h-[560px] md:min-h-[512px] overflow-hidden">
            <div
                className="w-full h-full flex cursor-grab active:cursor-grabbing select-none"
                style={{
                    transform: `translateX(-${position * 100}%)`,
                    transition: isTransitioning
                        ? "transform 0.7s ease-in-out"
                        : "none",
                }}
                onTransitionEnd={handleTransitionEnd}
                {...handlers}
            >
                {slidesWithClones.map((slide: Slide, index: number) => (
                    <div
                        key={index}
                        className="w-full h-full flex-shrink-0 bg-white dark:bg-gray-900 p-8"
                    >
                        <div className="container mx-auto flex flex-col-reverse md:flex-row items-center justify-center md:justify-between gap-8 h-full">
                            <div className="w-full md:w-5/12 flex flex-col gap-4 items-start text-left">
                                <h2
                                    onTouchStart={(e) => e.stopPropagation()}
                                    onMouseDown={(e) => e.stopPropagation()}
                                    className="text-3xl font-bold text-gray-900 dark:text-white cursor-auto select-text"
                                >
                                    {slide.title}
                                </h2>
                                <h3
                                    onTouchStart={(e) => e.stopPropagation()}
                                    onMouseDown={(e) => e.stopPropagation()}
                                    className="text-xl font-semibold text-gray-600 dark:text-gray-300 cursor-auto select-text"
                                >
                                    {slide.subtitle}
                                </h3>
                                <p
                                    onTouchStart={(e) => e.stopPropagation()}
                                    onMouseDown={(e) => e.stopPropagation()}
                                    className="text-gray-700 dark:text-gray-200 cursor-auto select-text"
                                >
                                    {slide.description}
                                </p>
                                <p
                                    onTouchStart={(e) => e.stopPropagation()}
                                    onMouseDown={(e) => e.stopPropagation()}
                                    className="text-gray-700 dark:text-gray-200 cursor-auto select-text"
                                >
                                    {slide.subDescription}
                                </p>
                                <button
                                    onTouchStart={(e) => e.stopPropagation()}
                                    onMouseDown={(e) => e.stopPropagation()}
                                    className="mt-2 w-max px-4 py-2 bg-blue-600 text-white rounded-md cursor-pointer"
                                >
                                    Saiba mais
                                </button>
                            </div>
                            <div className="w-full md:w-6/12 flex items-center justify-center select-none z-0">
                                <Image
                                    placeholder="blur"
                                    src={slide.image}
                                    alt={slide.title}
                                    priority={index === 1}
                                    draggable="false"
                                    className="w-full h-auto max-h-[400px] object-contain pointer-events-none"
                                />
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="absolute inset-x-0 bottom-5 flex justify-center z-10">
                <div className="flex items-center gap-2 p-1 bg-black/20 rounded-full">
                    <button
                        onClick={prev}
                        disabled={isNavigating}
                        aria-label="Previous slide"
                        className="p-1.5 bg-white/70 rounded-full shadow-md hover:bg-white transition-opacity disabled:opacity-50"
                    >
                        <ChevronLeft className="w-5 h-5 text-gray-800" />
                    </button>
                    <div className="flex items-center gap-2">
                        {slides.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => {
                                    if (isNavigating) return;
                                    setIsNavigating(true);
                                    setPosition(i + 1);
                                }}
                                aria-label={`Go to slide ${i + 1}`}
                                className={`h-1.5 rounded-full transition-all duration-300 ease-in-out ${
                                    currentIndex === i
                                        ? "w-6 bg-white"
                                        : "w-1.5 bg-white/60"
                                }`}
                            />
                        ))}
                    </div>
                    <button
                        onClick={next}
                        disabled={isNavigating}
                        aria-label="Next slide"
                        className="p-1.5 bg-white/70 rounded-full shadow-md hover:bg-white transition-opacity disabled:opacity-50"
                    >
                        <ChevronRight className="w-5 h-5 text-gray-800" />
                    </button>
                </div>
            </div>
        </section>
    );
}
