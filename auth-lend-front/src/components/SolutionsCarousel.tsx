"use client";

import { useEffect, useState } from "react";
import { Briefcase, ChevronLeft, ChevronRight } from "lucide-react";

interface Solution {
    id: number;
    title: string;
    description: string;
}

export default function SolutionsCarousel() {
    const [solutions, setSolutions] = useState<Solution[]>([]);
    const [itemsPerView, setItemsPerView] = useState(8);
    const [index, setIndex] = useState(0);

    useEffect(() => {
        const update = () => {
            setItemsPerView(window.innerWidth >= 1024 ? 8 : 2);
        };
        update();
        window.addEventListener("resize", update);
        return () => window.removeEventListener("resize", update);
    }, []);

    useEffect(() => {
        fetch("http://localhost:8000/api/v1/solutions?per_page=20")
            .then((res) => res.json())
            .then((data) => {
                const list = data?.data?.data ?? [];
                setSolutions(list);
            })
            .catch(() => {});
    }, []);

    const totalSlides = Math.ceil(solutions.length / itemsPerView) || 1;
    const chunks = [] as Solution[][];
    for (let i = 0; i < solutions.length; i += itemsPerView) {
        chunks.push(solutions.slice(i, i + itemsPerView));
    }

    const prev = () => setIndex((p) => (p - 1 + totalSlides) % totalSlides);
    const next = () => setIndex((p) => (p + 1) % totalSlides);

    return (
        <section className="py-12">
            <h2 className="text-center text-2xl font-bold mb-8">
                Nossas soluções
            </h2>
            <div className="relative overflow-hidden">
                <div
                    className="flex transition-transform duration-500"
                    style={{ transform: `translateX(-${index * 100}%)` }}
                >
                    {chunks.map((chunk, i) => (
                        <div
                            key={i}
                            className="shrink-0 w-full grid grid-cols-1 sm:grid-cols-2 gap-4 lg:grid-cols-4 lg:grid-rows-2 p-16"
                        >
                            {chunk.map((sol) => (
                                <div
                                    key={`${sol.id}-${sol.title}`}
                                    className="h-auto w-full bg-white dark:bg-gray-800 border rounded flex flex-col items-center justify-center p-4"
                                >
                                    <Briefcase className="w-6 h-6 mb-2" />
                                    <h3 className="font-semibold text-center mb-1 text-sm">
                                        {sol.title}
                                    </h3>
                                    <p className="text-xs text-center text-gray-600 dark:text-gray-400">
                                        {sol.description}
                                    </p>
                                </div>
                            ))}
                        </div>
                    ))}
                </div>
                <div className="absolute inset-0 flex items-center justify-between pointer-events-none">
                    <button
                        aria-label="Anterior"
                        onClick={prev}
                        className="pointer-events-auto ml-2 p-2 bg-white/70 rounded-full shadow hover:bg-white"
                    >
                        <ChevronLeft className="w-5 h-5" />
                    </button>
                    <button
                        aria-label="Próximo"
                        onClick={next}
                        className="pointer-events-auto mr-2 p-2 bg-white/70 rounded-full shadow hover:bg-white"
                    >
                        <ChevronRight className="w-5 h-5" />
                    </button>
                </div>
            </div>
        </section>
    );
}
