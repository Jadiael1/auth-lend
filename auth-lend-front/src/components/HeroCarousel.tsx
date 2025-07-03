"use client";

import Image from "next/image";
import { useKeenSlider } from "keen-slider/react";
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
  const [sliderRef, instanceRef] = useKeenSlider<HTMLDivElement>({
    loop: true,
    renderMode: "performance",
    slides: { perView: 1 },
  });

  return (
    <section className="relative">
      <div ref={sliderRef} className="keen-slider">
        {slides.map((slide, idx) => (
          <div key={idx} className="keen-slider__slide">
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
                <Image src={slide.image} alt="slide" fill className="object-cover" />
              </div>
            </div>
            {/* Mobile layout */}
            <div className="md:hidden relative h-80">
              <Image src={slide.image} alt="slide" fill className="object-cover" />
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
      <div className="absolute inset-0 flex items-center justify-between pointer-events-none">
        <button
          aria-label="Previous slide"
          onClick={() => instanceRef.current?.prev()}
          className="pointer-events-auto ml-4 p-2 bg-white/70 rounded-full shadow hover:bg-white"
        >
          <ChevronLeft className="w-5 h-5" />
        </button>
        <button
          aria-label="Next slide"
          onClick={() => instanceRef.current?.next()}
          className="pointer-events-auto mr-4 p-2 bg-white/70 rounded-full shadow hover:bg-white"
        >
          <ChevronRight className="w-5 h-5" />
        </button>
      </div>
    </section>
  );
}
