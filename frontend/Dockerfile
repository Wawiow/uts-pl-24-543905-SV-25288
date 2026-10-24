# Tahap 1: Build aplikasi Vue
FROM node:22-alpine AS build

WORKDIR /app

# Salin file dependency terlebih dahulu
COPY package.json package-lock.json ./

RUN npm ci

# Salin source code dan build aplikasi
COPY . .

ARG VITE_API_URL=/api
ENV VITE_API_URL=${VITE_API_URL}

RUN npm run build

# Tahap 2: Jalankan hasil build dengan Nginx
FROM nginx:1.28-alpine

COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

# Tahap 2: Jalankan hasil build dengan Nginx
FROM nginx:1.28-alpine

COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]