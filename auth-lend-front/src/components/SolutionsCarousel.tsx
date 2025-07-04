"use client";
import { useEffect, useState, useMemo, useCallback } from "react";
import {
    Briefcase,
    ChevronLeft,
    ChevronRight,
    AlertTriangle,
} from "lucide-react";
import { useSwipeable } from "react-swipeable";

interface Solution {
    id: number;
    title: string;
    description: string;
}

function SolutionCard({
    title,
    description,
}: {
    title: string;
    description: string;
}) {
    return (
        <div className="h-full w-full bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg flex flex-col items-center p-6 text-center shadow-sm hover:shadow-lg hover:scale-[1.03] transition-all duration-300">
            <div className="mb-4 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/50">
                <Briefcase className="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <h3
                onTouchStart={(e) => e.stopPropagation()}
                onMouseDown={(e) => e.stopPropagation()}
                className="flex-grow font-semibold text-gray-900 dark:text-white text-base mb-2 leading-tight cursor-auto select-text"
            >
                {title}
            </h3>
            <p
                onTouchStart={(e) => e.stopPropagation()}
                onMouseDown={(e) => e.stopPropagation()}
                className="text-sm text-gray-500 dark:text-gray-400 cursor-auto select-text"
            >
                {description}
            </p>
            <button
                onTouchStart={(e) => e.stopPropagation()}
                onMouseDown={(e) => e.stopPropagation()}
                className="mt-4 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
            >
                Saiba mais
            </button>
        </div>
    );
}

function SkeletonCard() {
    return (
        <div className="h-full w-full bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg flex flex-col items-center justify-center p-6 shadow-sm animate-pulse">
            <div className="mb-4 h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700" />
            <div className="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700 mb-3" />
            <div className="h-3 w-full rounded bg-gray-200 dark:bg-gray-700 mb-1" />
            <div className="h-3 w-1/2 rounded bg-gray-200 dark:bg-gray-700" />
        </div>
    );
}

export default function SolutionsCarousel() {
    const [solutions, setSolutions] = useState<Solution[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [itemsPerView, setItemsPerView] = useState(8);
    const [index, setIndex] = useState(1);
    const [isTransitioning, setIsTransitioning] = useState(true);
    const [isNavigating, setIsNavigating] = useState(false);

    useEffect(() => {
        setIsLoading(true);
        setError(null);
        fetch(`http://localhost:8000/api/v1/solutions?per_page=20`)
            .then((res) => {
                if (!res.ok) throw new Error("Falha ao buscar soluções.");
                return res.json();
            })
            .then((data) => {
                setSolutions(data?.data?.data ?? []);
            })
            .catch((err) => {
                setError(err.message || "Ocorreu um erro inesperado.");
            })
            .finally(() => {
                setIsLoading(false);
            });
    }, []);

    useEffect(() => {
        const handleResize = () => {
            const width = window.innerWidth;
            if (width >= 1024) setItemsPerView(8);
            else if (width >= 640) setItemsPerView(4);
            else setItemsPerView(2);
        };
        handleResize();
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    const chunks = useMemo(() => {
        const result: Solution[][] = [];
        if (solutions.length > 0) {
            for (let i = 0; i < solutions.length; i += itemsPerView) {
                result.push(solutions.slice(i, i + itemsPerView));
            }
        }
        return result;
    }, [solutions, itemsPerView]);

    useEffect(() => {
        setIndex(1);
    }, [chunks.length]);

    const chunkSlides = useMemo(
        () =>
            chunks.length
                ? [chunks[chunks.length - 1], ...chunks, chunks[0]]
                : [],
        [chunks]
    );

    const prev = useCallback(() => {
        if (isNavigating) return;
        setIsNavigating(true);
        setIndex((p) => p - 1);
    }, [isNavigating]);

    const next = useCallback(() => {
        if (isNavigating) return;
        setIsNavigating(true);
        setIndex((p) => p + 1);
    }, [isNavigating]);

    const handleTransitionEnd = () => {
        setIsNavigating(false);
        if (index === 0) {
            setIsTransitioning(false);
            setIndex(chunkSlides.length - 2);
        } else if (index === chunkSlides.length - 1) {
            setIsTransitioning(false);
            setIndex(1);
        }
    };

    useEffect(() => {
        if (!isTransitioning) {
            requestAnimationFrame(() => setIsTransitioning(true));
        }
    }, [isTransitioning]);

    const handlers = useSwipeable({
        onSwipedLeft: () => next(),
        onSwipedRight: () => prev(),
        trackMouse: true,
    });

    const renderContent = () => {
        if (isLoading) {
            return (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-4 md:p-6">
                    {Array.from({ length: 8 }).map((_, i) => (
                        <SkeletonCard key={i} />
                    ))}
                </div>
            );
        }
        if (error) {
            return (
                <div className="flex flex-col items-center justify-center text-center text-red-500 bg-red-50 dark:bg-red-900/20 py-12 px-4 rounded-lg">
                    <AlertTriangle className="w-10 h-10 mb-4" />
                    <h3 className="font-semibold text-lg">
                        Não foi possível carregar as soluções
                    </h3>
                    <p className="text-sm">{error}</p>
                </div>
            );
        }
        if (solutions.length === 0) {
            return (
                <div className="text-center py-12">
                    <p>Nenhuma solução encontrada.</p>
                </div>
            );
        }

        return (
            <div
                {...handlers}
                className="relative overflow-hidden cursor-grab active:cursor-grabbing select-none"
            >
                <div
                    className="flex"
                    onTransitionEnd={handleTransitionEnd}
                    style={{
                        transform: `translateX(-${index * 100}%)`,
                        transition: isTransitioning
                            ? "transform 0.5s ease-in-out"
                            : "none",
                    }}
                >
                    {chunkSlides.map((chunk, i) => (
                        <div
                            key={i}
                            className="shrink-0 w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-4 md:p-6"
                        >
                            {chunk.map((sol) => (
                                <SolutionCard key={sol.id} {...sol} />
                            ))}
                            {chunk.length < itemsPerView &&
                                Array.from({
                                    length: itemsPerView - chunk.length,
                                }).map((_, fillIndex) => (
                                    <div
                                        key={`fill-${fillIndex}`}
                                        className="hidden lg:block"
                                    ></div>
                                ))}
                        </div>
                    ))}
                </div>

                <div className="absolute inset-y-0 flex items-center justify-between w-full pointer-events-none px-2">
                    <button
                        aria-label="Anterior"
                        onClick={prev}
                        disabled={isNavigating}
                        className="pointer-events-auto p-2 bg-white/80 dark:bg-gray-900/80 rounded-full shadow-md hover:bg-white dark:hover:bg-gray-800 transition-opacity disabled:opacity-50"
                    >
                        <ChevronLeft className="w-6 h-6" />
                    </button>
                    <button
                        aria-label="Próximo"
                        onClick={next}
                        disabled={isNavigating}
                        className="pointer-events-auto p-2 bg-white/80 dark:bg-gray-900/80 rounded-full shadow-md hover:bg-white dark:hover:bg-gray-800 transition-opacity disabled:opacity-50"
                    >
                        <ChevronRight className="w-6 h-6" />
                    </button>
                </div>
            </div>
        );
    };

    useEffect(() => {
        const timer = setInterval(next, 8000);
        return () => clearInterval(timer);
    }, [next]);

    return (
        <section className="py-12 bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4">
                <h2 className="text-center text-3xl font-bold mb-8 text-gray-900 dark:text-white">
                    Nossas soluções
                </h2>
                {renderContent()}
            </div>
        </section>
    );
}
