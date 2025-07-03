import Navbar from "../components/Navbar";
import HeroCarousel from "../components/HeroCarousel";
import SolutionsCarousel from "../components/SolutionsCarousel";

export default function Home() {
  return (
    <>
      <Navbar />
      <main>
        <HeroCarousel />
        <SolutionsCarousel />
      </main>
    </>
  );
}
