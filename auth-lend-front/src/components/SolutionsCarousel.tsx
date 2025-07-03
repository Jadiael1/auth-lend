"use client";

import { useEffect, useState } from "react";
import { useKeenSlider } from "keen-slider/react";
import "keen-slider/keen-slider.min.css";
import { Briefcase } from "lucide-react";

interface Solution {
  id: number;
  title: string;
  description: string;
}

export default function SolutionsCarousel() {
  const [solutions, setSolutions] = useState<Solution[]>([]);
  const [sliderRef, instanceRef] = useKeenSlider<HTMLDivElement>({
    loop: false,
    slides: { perView: 2, spacing: 16 },
    breakpoints: {
      "(min-width: 768px)": { slides: { perView: 4, spacing: 16 } },
      "(min-width: 1280px)": { slides: { perView: 8, spacing: 16 } },
    },
    renderMode: "performance",
  });

  useEffect(() => {
    fetch("http://localhost:8000/api/v1/solutions?per_page=20")
      .then((res) => res.json())
      .then((data) => {
        const list = data?.data?.data ?? [];
        setSolutions(list);
      })
      .catch(() => {});
  }, []);

  return (
    <section className="py-12">
      <h2 className="text-center text-2xl font-bold mb-8">Nossas soluções</h2>
      <div className="relative">
        <div ref={sliderRef} className="keen-slider">
          {solutions.map((sol) => (
            <div key={sol.id} className="keen-slider__slide">
              <div className="h-40 w-full bg-white dark:bg-gray-800 border rounded flex flex-col items-center justify-center p-4">
                <Briefcase className="w-6 h-6 mb-2" />
                <h3 className="font-semibold text-center mb-1 text-sm">
                  {sol.title}
                </h3>
                <p className="text-xs text-center text-gray-600 dark:text-gray-400">
                  {sol.description}
                </p>
              </div>
            </div>
          ))}
        </div>
        <div className="absolute inset-0 flex items-center justify-between pointer-events-none">
          <button
            aria-label="Anterior"
            onClick={() => instanceRef.current?.prev()}
            className="pointer-events-auto ml-2 p-1 bg-white/70 rounded-full shadow hover:bg-white"
          >
            <span className="sr-only">Anterior</span>
            &#8249;
          </button>
          <button
            aria-label="Próximo"
            onClick={() => instanceRef.current?.next()}
            className="pointer-events-auto mr-2 p-1 bg-white/70 rounded-full shadow hover:bg-white"
          >
            <span className="sr-only">Próximo</span>
            &#8250;
          </button>
        </div>
      </div>
    </section>
  );
}
