-- Предполагается, что в одном заказе не может быть несколько разных товаров,
-- иначе понадобится дополнительная промежуточная таблица

CREATE TABLE IF NOT EXISTS "categories" (
    "category_id" SERIAL NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP,
    "deleted_at" TIMESTAMP ,
    PRIMARY KEY ("category_id")
    );

CREATE TABLE IF NOT EXISTS "products" (
    "product_id" BIGSERIAL NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "product_category_id" INTEGER NOT NULL REFERENCES "categories" ("category_id"),
    "price" NUMERIC NOT NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP,
    "deleted_at" TIMESTAMP,
    PRIMARY KEY ("product_id")
    );

CREATE TABLE IF NOT EXISTS "orders" (
    "order_id" BIGSERIAL NOT NULL,
    "order_date" TIMESTAMP NOT NULL,
    "product_id" INTEGER REFERENCES "products"("product_id"),
    "quantity" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "phone" VARCHAR(30) NOT NULL,
    PRIMARY KEY ("order_id")
    );

CREATE TABLE IF NOT EXISTS  "stats" (
    "date" DATE NOT NULL,
    "category_id" INTEGER NOT NULL REFERENCES "categories"("category_id"),
    "quantity" INTEGER NOT NULL,
    PRIMARY KEY ("date", "category_id")
);

CREATE OR REPLACE FUNCTION update_stats()
    RETURNS TRIGGER AS $$
BEGIN
INSERT INTO stats (date, category_id, quantity)
SELECT date(new.order_date) as date, p.product_category_id as category_id, NEW.quantity
from products p where p.product_id=NEW.product_id
ON CONFLICT ("date", "category_id")
    DO UPDATE SET quantity = stats.quantity + excluded.quantity;
RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_insert_orders AFTER INSERT on orders
    FOR EACH ROW
    EXECUTE FUNCTION update_stats();

