FROM node:20-alpine

WORKDIR /app

COPY frontend/package*.json ./

RUN if [ -f package.json ]; then npm install; fi

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]