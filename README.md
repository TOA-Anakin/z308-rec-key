# Z308-REC-KEY

A boilerplate repo to facilitate testing connections to the Aleph Oracle DB. 

In this demo: generation of values for `Z308_REC_KEY` based on `Z303` records.

## Dev with Docker

### Building

```sh
sudo docker build -t z308reckey .
```

### Starting

```sh
sudo docker run -p 8080:8080 -v ./app:/app --name z308reckey-php z308reckey
```

Go to: http://localhost:8080/

### Stopping, removing

```sh
sudo docker stop <_container_id_>
sudo docker remove <_container_id_>
sudo docker rim <_image_id_>
```