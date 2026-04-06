# API Transactions

Documentation des endpoints de l'API de gestion des transactions.

> **Authentification** : Tous les endpoints protégés nécessitent le header `X-User-Id`. Sans ce header, l'API retourne `401 Unauthorized`.

---

## 1. Créer une transaction

**`POST /api/transactions`**

Crée une nouvelle transaction de type `income` (entrée) ou `outcome` (sortie).

### Headers

| Header      | Requis | Description                  |
| ----------- | ------ | ---------------------------- |
| `X-User-Id` | Oui    | Identifiant de l'utilisateur |

### Body

```json
{
  "libelle": "string",
  "montant": number,
  "type": "income" | "outcome"
}
```

### Réponses

| Cas                                     | Code  | Réponse                                    |
| --------------------------------------- | ----- | ------------------------------------------ |
| Transaction `income` créée avec succès  | `201` | `{ "status": "success", "data": { ... } }` |
| Transaction `outcome` créée avec succès | `201` | `{ "status": "success", "data": { ... } }` |
| Header `X-User-Id` manquant             | `401` | `{ "status": "unauthorized" }`             |
| Payload invalide ou incomplet           | `400` | `{ "status": "validation_error" }`         |

### Exemple de requête

```http
POST /api/transactions
X-User-Id: user-123
Content-Type: application/json

{
  "libelle": "Salaire mars",
  "montant": 2500,
  "type": "income"
}
```

### Exemple de réponse (`201`)

```json
{
  "status": "success",
  "data": {
    "id": "txn-abc123",
    "libelle": "Salaire mars",
    "montant": 2500,
    "type": "income",
    "status": "pending",
    "userId": "user-123"
  }
}
```

---

## 2. Afficher une transaction

**`GET /api/transactions/{id}`**

Retourne les détails d'une transaction existante appartenant à l'utilisateur authentifié.

### Headers

| Header      | Requis | Description                  |
| ----------- | ------ | ---------------------------- |
| `X-User-Id` | Oui    | Identifiant de l'utilisateur |

### Paramètres de chemin

| Paramètre | Type   | Description                          |
| --------- | ------ | ------------------------------------ |
| `id`      | string | Identifiant unique de la transaction |

### Réponses

| Cas                         | Code  | Réponse                                    |
| --------------------------- | ----- | ------------------------------------------ |
| Transaction trouvée         | `200` | `{ "status": "success", "data": { ... } }` |
| Transaction inexistante     | `404` | `{ "status": "not_found" }`                |
| Header `X-User-Id` manquant | `401` | `{ "status": "unauthorized" }`             |

### Exemple de requête

```http
GET /api/transactions/txn-abc123
X-User-Id: user-123
```

### Exemple de réponse (`200`)

```json
{
  "status": "success",
  "data": {
    "id": "txn-abc123",
    "libelle": "Salaire mars",
    "montant": 2500,
    "type": "income",
    "status": "pending",
    "userId": "user-123"
  }
}
```

---

## 3. Mettre à jour le statut d'une transaction

**`PATCH /api/transactions/{id}`**

Met à jour le statut d'une transaction existante. Seules les transitions de statut valides sont autorisées.

### Headers

| Header      | Requis | Description                                                 |
| ----------- | ------ | ----------------------------------------------------------- |
| `X-User-Id` | Oui    | Identifiant de l'utilisateur propriétaire de la transaction |

### Paramètres de chemin

| Paramètre | Type   | Description                          |
| --------- | ------ | ------------------------------------ |
| `id`      | string | Identifiant unique de la transaction |

### Body

```json
{
  "status": "completed" | "cancelled" | "failed"
}
```

### Réponses

| Cas                                              | Code  | Réponse                                                           |
| ------------------------------------------------ | ----- | ----------------------------------------------------------------- |
| Statut mis à jour → `completed`                  | `200` | `{ "status": "success", "data": { "status": "completed", ... } }` |
| Statut mis à jour → `cancelled`                  | `200` | `{ "status": "success", "data": { "status": "cancelled", ... } }` |
| Statut mis à jour → `failed`                     | `200` | `{ "status": "success", "data": { "status": "failed", ... } }`    |
| Header `X-User-Id` manquant                      | `401` | `{ "status": "unauthorized" }`                                    |
| Transaction inexistante                          | `404` | `{ "status": "not_found" }`                                       |
| Transaction appartenant à un autre utilisateur   | `404` | `{ "status": "not_found" }`                                       |
| Transition de statut invalide (ex. `pending`)    | `400` | —                                                                 |
| Valeur de statut invalide (ex. `invalid_status`) | `400` | `{ "status": "validation_error" }`                                |

> **Note** : Une transaction appartenant à un autre utilisateur retourne `404` (et non `403`) afin de ne pas révéler l'existence de la ressource.

### Transitions de statut autorisées

```
pending ──→ completed
pending ──→ cancelled
pending ──→ failed
```

Toute autre transition (ex. tenter de repasser en `pending`) est rejetée avec un code `400`.

### Exemple de requête

```http
PATCH /api/transactions/txn-abc123
X-User-Id: user-123
Content-Type: application/json

{
  "status": "completed"
}
```

### Exemple de réponse (`200`)

```json
{
  "status": "success",
  "data": {
    "id": "txn-abc123",
    "libelle": "Salaire mars",
    "montant": 2500,
    "type": "income",
    "status": "completed",
    "userId": "user-123"
  }
}
```
