# Multi-stage build for Falls Origin Coffee

# Stage 1: Build frontend
FROM node:20-alpine AS frontend-builder

WORKDIR /app/frontend

# Copy frontend package files
COPY package.json ./
COPY tsconfig.json ./
COPY vite.config.ts ./
COPY index.html ./

# Copy frontend source
COPY components ./components
COPY pages ./pages
COPY services ./services
COPY types.ts ./
COPY constants.tsx ./
COPY App.tsx ./
COPY index.tsx ./

# Install dependencies and build
RUN npm install --legacy-peer-deps
RUN npm run build

# Stage 2: Build backend
FROM node:20-alpine AS backend-builder

WORKDIR /app/server

# Copy backend package files
COPY server/package.json ./
COPY server/tsconfig.json ./

# Copy backend source
COPY server/src ./src

# Install dependencies (including devDependencies for build)
RUN npm install --legacy-peer-deps

# Build TypeScript
RUN npm run build

# Stage 3: Production image
FROM node:20-alpine

WORKDIR /app

# Install production dependencies only
COPY server/package.json ./
RUN npm install --production

# Copy built backend
COPY --from=backend-builder /app/server/dist ./dist

# Copy built frontend
COPY --from=frontend-builder /app/frontend/dist ./dist-frontend

# Copy migrations
COPY server/migrations ./migrations

# Create logs directory
RUN mkdir -p logs

# Expose port (Cloud Run uses PORT env var)
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s \
  CMD node -e "require('http').get('http://localhost:8080/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

# Start server
CMD ["node", "dist/index.js"]
