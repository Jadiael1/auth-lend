"use client";

import Image from "next/image";
import { useState } from "react";
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
    title: "Bem-vindo",
    subtitle: "Soluções de crédito",
    description: "Facilidade e rapidez para você",
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

export default function HeroCarousel() {
  const [index, setIndex] = useState(0);
  const prev = () => setIndex((prev) => (prev - 1 + slides.length) % slides.length);
  const next = () => setIndex((prev) => (prev + 1) % slides.length);

  return (
    <section className="relative overflow-hidden">
      <div
        className="flex transition-transform duration-500"
        style={{ transform: `translateX(-${index * 100}%)` }}
      >
        {slides.map((slide, idx) => (
          <div key={idx} className="w-full shrink-0">
            {/* Desktop layout */}
            <div className="hidden md:grid grid-cols-2 h-80 md:h-[32rem]">
              <div className="flex flex-col justify-center gap-4 p-8 md:p-12">
                <h2 className="text-3xl font-bold">{slide.title}</h2>
                <h3 className="text-xl font-semibold text-gray-600 dark:text-gray-300">
                  {slide.subtitle}
                </h3>
                <p className="text-gray-700 dark:text-gray-200">{slide.description}</p>
                <p className="text-gray-700 dark:text-gray-200">
                  {slide.subDescription}
                </p>
                <button className="w-max px-4 py-2 bg-blue-600 text-white rounded-md">
                  Saiba mais
                </button>
              </div>
              <div className="relative">
                <Image
                  src={slide.image}
                  alt="slide"
                  fill
                  className="object-cover object-right"
                />
                <div className="absolute inset-0 bg-gradient-to-l from-transparent to-white dark:to-gray-900" />
              </div>
            </div>
            {/* Mobile layout */}
            <div className="md:hidden relative h-80">
              <Image src={slide.image} alt="slide" fill className="object-cover object-right" />
              <div className="absolute inset-0 bg-black/50 flex flex-col justify-end p-4 gap-2 text-white">
                <h2 className="text-2xl font-bold">{slide.title}</h2>
                <h3 className="text-lg font-semibold">{slide.subtitle}</h3>
                <p>{slide.description}</p>
                <p>{slide.subDescription}</p>
                <button className="mt-2 w-max px-4 py-2 bg-blue-600 text-white rounded-md">
                  Saiba mais
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
      <div className="pointer-events-none absolute inset-0 md:left-1/2 md:w-1/2 flex items-center justify-between">
        <button
          aria-label="Previous slide"
          onClick={prev}
          className="pointer-events-auto ml-4 p-2 bg-white/70 rounded-full shadow hover:bg-white"
        >
          <ChevronLeft className="w-5 h-5" />
        </button>
        <button
          aria-label="Next slide"
          onClick={next}
          className="pointer-events-auto mr-4 p-2 bg-white/70 rounded-full shadow hover:bg-white"
        >
          <ChevronRight className="w-5 h-5" />
        </button>
      </div>
    </section>
  );
}