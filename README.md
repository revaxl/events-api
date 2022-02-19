# Events API
## How to run:

- Clone the project : `git clone https://github.com/revaxl/events-api.git`
- Build the docker image : `docker build -t events-api .`
- Run the docker image : `docker run -p 8000:8000 events-api`

## Accessible URLS:
**All REQUESTS SHOULD HAVE `Accept: application/json` IN THE HEADER** 
- List events : `GET http://localhost:8000/api/events`

- Create new Reservation for event: `POST http://localhost:8000/api/events/<event id>/reservation`

  - Data required in the request body : `user_id: <integer>, reservations: <integer>`

- Update Reservation for event: `PUT http://localhost:8000/api/events/<event id>/reservation`

    - Data required in the request body : `user_id: <integer>, reservations: <integer>`

- Cancel Reservation for event: `DELETE http://localhost:8000/api/events/<event id>/cancel`

    - Data required in the request body : `user_id: <integer>`
