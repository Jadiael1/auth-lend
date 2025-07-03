import type { NextConfig } from "next";

const picsumPhotosUrl = new URL('https://picsum.photos');

const remotePattern = {
	protocol: picsumPhotosUrl.protocol.replace(':', '') as 'http' | 'https',
	hostname: picsumPhotosUrl.hostname,
	pathname: '/id/**',
	port: picsumPhotosUrl.port || '',
};

const nextConfig: NextConfig = {
  /* config options here */
  images: {
		remotePatterns: [remotePattern],
	},
};

export default nextConfig;
