import type { NextConfig } from "next";

const imagesUrl = [
    new URL("https://picsum.photos"),
    new URL("http://localhost:8000"),
];

const remotePattern = imagesUrl.map(imageUrl => ({
	protocol: imageUrl.protocol.replace(':', '') as 'http' | 'https',
	hostname: imageUrl.hostname,
	pathname: '/**',
	port: imageUrl.port || '',
}));

const nextConfig: NextConfig = {
  /* config options here */
  images: {
		remotePatterns: remotePattern,
	},
};

export default nextConfig;
