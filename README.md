# Z308-REC-KEY

A boilerplate repo to facilitate testing connections to the Aleph Oracle DB. 

In this demo: generation of values for `Z308_REC_KEY` based on `Z303` records.

## Dev with Docker

### Containers config

- `.docker/.env.nginx.local`

### Oracle DB config

- `.env` based on `.env.example`

### Building

```sh
cd .docker
docker compose up -d --build
```

Then, go to http://localhost:PORT_HOST/