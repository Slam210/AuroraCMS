# Stage 1: Build Angular app
FROM node:24.9.0-alpine3.21 AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci --legacy-peer-deps
COPY . .
RUN npm run build -- --configuration production

# Stage 2: Serve with Nginx
FROM nginx:alpine
# Remove default content
RUN rm -rf /usr/share/nginx/html/*
# Copy built Angular app
COPY --from=build /app/dist/frontend /usr/share/nginx/html
# Copy custom Nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
