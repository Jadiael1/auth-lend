"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

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
    },
    {
        title: "Atendimento Especial",
        subtitle: "Suporte dedicado",
        description: "Fale com especialistas quando precisar",
        subDescription: "Estamos prontos para ajudar",
        image: "https://picsum.photos/id/1015/1000/600",
    },
    {
        title: "Segurança",
        subtitle: "Seus dados protegidos",
        description: "Tecnologia e privacidade",
        subDescription: "Conte conosco",
        image: "https://picsum.photos/id/1016/1000/600",
    },
    {
        title: "Transparência",
        subtitle: "Informações claras",
        description: "Tudo detalhado para você",
        subDescription: "Sem surpresas",
        image: "https://picsum.photos/id/1021/1000/600",
    },
    {
        title: "Praticidade",
        subtitle: "Processos simplificados",
        description: "Agilidade no seu dia a dia",
        subDescription: "Solicite já",
        image: "https://picsum.photos/id/1020/1000/600",
    },
    {
        title: "Confiabilidade",
        subtitle: "Anos de experiência",
        description: "Tradição no mercado",
        subDescription: "Faça parte",
        image: "https://picsum.photos/id/1024/1000/600",
    },
];

type Direction = "next" | "prev";

export default function HeroCarousel() {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [prevIndex, setPrevIndex] = useState<number | null>(null);
    const [direction, setDirection] = useState<Direction>("next");
    const [isInitial, setIsInitial] = useState(true);

    const next = () => {
        if (isInitial) setIsInitial(false);
        setPrevIndex(currentIndex);
        setDirection("next");
        setCurrentIndex((prev) => (prev + 1) % slides.length);
    };

    const prev = () => {
        if (isInitial) setIsInitial(false);
        setPrevIndex(currentIndex);
        setDirection("prev");
        setCurrentIndex((prev) => (prev - 1 + slides.length) % slides.length);
    };

    useEffect(() => {
        if (isInitial) return;
        const timeout = setTimeout(() => {
            setPrevIndex(null);
        }, 700);
        return () => clearTimeout(timeout);
    }, [currentIndex, isInitial]);

    const getSlideClass = (type: "current" | "prev") => {
        if (isInitial) return;
        if (type === "current") {
            return direction === "next"
                ? "animate-slide-in-from-right"
                : "animate-slide-in-from-left";
        } else {
            return direction === "next"
                ? "animate-slide-out-to-left"
                : "animate-slide-out-to-right";
        }
    };

    const renderSlideContent = (slide: Slide) => (
        <>
            <Image
                src={slide.image}
                alt={slide.title}
                fill
                priority
                sizes="100vw"
                className="object-cover md:object-right"
            />
            <div className="hidden md:block absolute inset-0 bg-gradient-to-l from-transparent via-white/70 to-white dark:via-gray-900/70 dark:to-gray-900" />
            <div className="hidden md:flex absolute inset-y-0 left-0 w-1/2 flex-col justify-center gap-4 p-22">
                <h2 className="text-3xl font-bold">{slide.title}</h2>
                <h3 className="text-xl font-semibold text-gray-600 dark:text-gray-300">
                    {slide.subtitle}
                </h3>
                <p className="text-gray-700 dark:text-gray-200">
                    {slide.description}
                </p>
                <p className="text-gray-700 dark:text-gray-200">
                    {slide.subDescription}
                </p>
                <button className="w-max px-4 py-2 bg-blue-600 text-white rounded-md">
                    Saiba mais
                </button>
            </div>
            <div className="md:hidden absolute inset-0 bg-black/50 flex flex-col justify-end p-18 gap-2 text-white">
                <h2 className="text-2xl font-bold">{slide.title}</h2>
                <h3 className="text-lg font-semibold">{slide.subtitle}</h3>
                <p>{slide.description}</p>
                <p>{slide.subDescription}</p>
                <button className="mt-2 w-max px-4 py-2 bg-blue-600 text-white rounded-md">
                    Saiba mais
                </button>
            </div>
        </>
    );

    return (
        <section className="relative overflow-hidden">
            <div className="relative w-full h-80 md:h-[32rem]">
                <div
                    key={`current-${currentIndex}`}
                    className={`absolute inset-0 transition-transform duration-700 ease-in-out ${getSlideClass(
                        "current"
                    )}`}
                >
                    {renderSlideContent(slides[currentIndex])}
                </div>

                {prevIndex !== null && (
                    <div
                        key={`prev-${prevIndex}`}
                        className={`absolute inset-0 transition-transform duration-700 ease-in-out ${getSlideClass(
                            "prev"
                        )}`}
                    >
                        {renderSlideContent(slides[prevIndex])}
                    </div>
                )}
            </div>

            <div className="pointer-events-none absolute inset-0 flex items-center justify-between z-20">
                <button
                    onClick={prev}
                    aria-label="Previous slide"
                    className="pointer-events-auto ml-4 p-2 bg-white/70 rounded-full shadow hover:bg-white cursor-pointer"
                >
                    <ChevronLeft className="w-5 h-5" />
                </button>
                <button
                    onClick={next}
                    aria-label="Next slide"
                    className="pointer-events-auto mr-4 p-2 bg-white/70 rounded-full shadow hover:bg-white cursor-pointer"
                >
                    <ChevronRight className="w-5 h-5" />
                </button>
            </div>

            <style jsx>{`
                .animate-slide-in-from-right {
                    animation: slide-in-from-right 0.7s forwards;
                }
                .animate-slide-in-from-left {
                    animation: slide-in-from-left 0.7s forwards;
                }
                .animate-slide-out-to-left {
                    animation: slide-out-to-left 0.7s forwards;
                }
                .animate-slide-out-to-right {
                    animation: slide-out-to-right 0.7s forwards;
                }

                @keyframes slide-in-from-right {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0%);
                        opacity: 1;
                    }
                }

                @keyframes slide-in-from-left {
                    from {
                        transform: translateX(-100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0%);
                        opacity: 1;
                    }
                }

                @keyframes slide-out-to-left {
                    from {
                        transform: translateX(0%);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(-100%);
                        opacity: 0;
                    }
                }

                @keyframes slide-out-to-right {
                    from {
                        transform: translateX(0%);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `}</style>
        </section>
    );
}
