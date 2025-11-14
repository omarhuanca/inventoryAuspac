/* **********************************************
 * SEQUENCES
 ************************************************/

CREATE SEQUENCE brd_seq;
CREATE SEQUENCE sbr_seq;
CREATE SEQUENCE cn_seq;
CREATE SEQUENCE ms_seq;
CREATE SEQUENCE spl_seq;
CREATE SEQUENCE prd_seq;
CREATE SEQUENCE bdl_seq;
CREATE SEQUENCE bdp_seq;
CREATE SEQUENCE stb_seq;
CREATE SEQUENCE str_seq;

/*==============================================================*/
/* Table: Brand                                                 */
/*==============================================================*/
CREATE TABLE brand (
    id      BIGINT          NOT NULL,
    code    VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
);

ALTER TABLE brand
    ALTER COLUMN id SET DEFAULT nextval('brd_seq'),
    ADD CONSTRAINT pk_brand_id PRIMARY KEY(id),
    ADD CONSTRAINT uq_brand_code UNIQUE(code);

/*==============================================================*/
/* Table: SubBrand                                              */
/*==============================================================*/
CREATE TABLE sub_brand (
    id          BIGINT          NOT NULL,
    code        VARCHAR(255)    NOT NULL,
    brand_id    BIGINT          NOT NULL,
    created_at TIMESTAMP        NULL,
    updated_at TIMESTAMP        NULL
);

ALTER TABLE sub_brand
    ALTER COLUMN id SET DEFAULT nextval('sbr_seq'),
    ADD CONSTRAINT pk_sub_brand_id PRIMARY KEY(id),
    ADD CONSTRAINT fk_sbr_brand_id FOREIGN KEY(brand_id)
        REFERENCES brand(id) ON UPDATE CASCADE ON DELETE CASCADE;

/*==============================================================*/
/* Table: Coin                                                  */
/*==============================================================*/
CREATE TABLE coin (
    id      BIGINT          NOT NULL,
    code    VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
);

ALTER TABLE coin
    ALTER COLUMN id SET DEFAULT nextval('cn_seq'),
    ADD CONSTRAINT pk_coin_id PRIMARY KEY(id),
    ADD CONSTRAINT uq_coin_code UNIQUE(code);

/*==============================================================*/
/* Table: Measure                                               */
/*==============================================================*/
CREATE TABLE measure (
    id      BIGINT          NOT NULL,
    code    VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
);

ALTER TABLE measure
    ALTER COLUMN id SET DEFAULT nextval('ms_seq'),
    ADD CONSTRAINT pk_measure_id PRIMARY KEY(id),
    ADD CONSTRAINT uq_measure_code UNIQUE(code);

/*==============================================================*/
/* Table: Supplier                                              */
/*==============================================================*/
CREATE TABLE supplier (
    id          BIGINT          NOT NULL,
    name        VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP        NULL,
    updated_at TIMESTAMP        NULL
);

ALTER TABLE supplier
    ALTER COLUMN id SET DEFAULT nextval('spl_seq'),
    ADD CONSTRAINT pk_supplier_id PRIMARY KEY(id),
    ADD CONSTRAINT uq_supplier_name UNIQUE(name);

/*==============================================================*/
/* Table: Product                                               */
/*==============================================================*/
CREATE TABLE product (
    id                      BIGINT          NOT NULL,
    code                    VARCHAR(255)    NOT NULL,
    supplier_cost_price     NUMERIC(10,2)   NOT NULL,
    supplier_coin_id        BIGINT          NOT NULL,
    landing_cost_price      NUMERIC(10,2)   NOT NULL,
    landing_coin_id         BIGINT          NOT NULL,
    retail_price            NUMERIC(10,2)   NOT NULL,
    promotional_price       NUMERIC(10,2)   NOT NULL,
    stock                   INTEGER         NOT NULL,
    measure_id              BIGINT          NOT NULL,
    serial_tracking         VARCHAR(255)    NOT NULL,
    dimension_size          VARCHAR(255)    NOT NULL,
    dimension_weight        INTEGER         NOT NULL,
    sub_brand_id            BIGINT          NOT NULL,
    supplier_id             BIGINT          NOT NULL,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
);

ALTER TABLE product
    ALTER COLUMN id SET DEFAULT nextval('prd_seq'),
    ADD CONSTRAINT pk_product_id PRIMARY KEY(id),
    ADD CONSTRAINT fk_prd_supplier_coin FOREIGN KEY(supplier_coin_id)
        REFERENCES coin(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_prd_landing_coin FOREIGN KEY(landing_coin_id)
    REFERENCES coin(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_prd_measure_id FOREIGN KEY(measure_id)
    REFERENCES measure(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_prd_sub_brand FOREIGN KEY(sub_brand_id)
    REFERENCES sub_brand(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_prd_supplier FOREIGN KEY(supplier_id)
    REFERENCES supplier(id) ON UPDATE CASCADE ON DELETE RESTRICT;

/*==============================================================*/
/* Table: Bundle                                                */
/*==============================================================*/
CREATE TABLE bundle (
    id                  BIGINT          NOT NULL,
    code                VARCHAR(255)    NOT NULL,
    landing_cost_price  NUMERIC(10,2)   NOT NULL,
    landing_coin_id     BIGINT          NOT NULL,
    retail_price        NUMERIC(10,2)   NOT NULL,
    promotional_price   NUMERIC(10,2)   NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE bundle
    ALTER COLUMN id SET DEFAULT nextval('bdl_seq'),
    ADD CONSTRAINT pk_bundle_id PRIMARY KEY(id),
    ADD CONSTRAINT uq_bundle_code UNIQUE(code),
    ADD CONSTRAINT fk_bundle_coin FOREIGN KEY(landing_coin_id)
        REFERENCES coin(id) ON UPDATE CASCADE ON DELETE RESTRICT;

/*==============================================================*/
/* Table: BundleProduct                                         */
/*==============================================================*/
CREATE TABLE bundle_product (
    id          BIGINT      NOT NULL,
    bundle_id   BIGINT      NOT NULL,
    product_id  BIGINT      NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE bundle_product
    ALTER COLUMN id SET DEFAULT nextval('bdp_seq'),
    ADD CONSTRAINT pk_bdp_id PRIMARY KEY(id),
    ADD CONSTRAINT fk_bdp_bundle FOREIGN KEY(bundle_id)
        REFERENCES bundle(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_bdp_product FOREIGN KEY(product_id)
    REFERENCES product(id) ON UPDATE CASCADE ON DELETE RESTRICT;

/*==============================================================*/
/* Table: StockBuy                                              */
/*==============================================================*/
CREATE TABLE stock_buy (
    id              BIGINT          NOT NULL,
    product_id      BIGINT          NOT NULL,
    amount          INTEGER         NOT NULL,
    date            DATE            NOT NULL,
    description     VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE stock_buy
    ALTER COLUMN id SET DEFAULT nextval('stb_seq'),
    ADD CONSTRAINT pk_stock_buy_id PRIMARY KEY(id),
    ADD CONSTRAINT fk_stb_product_id FOREIGN KEY(product_id)
        REFERENCES product(id) ON UPDATE CASCADE ON DELETE RESTRICT;

/*==============================================================*/
/* Table: StockReferral                                         */
/*==============================================================*/
CREATE TABLE stock_referral (
    id              BIGINT          NOT NULL,
    product_id      BIGINT          NOT NULL,
    amount          INTEGER         NOT NULL,
    date            DATE            NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE stock_referral
    ALTER COLUMN id SET DEFAULT nextval('str_seq'),
    ADD CONSTRAINT pk_stock_referral_id PRIMARY KEY(id),
    ADD CONSTRAINT fk_str_product_id FOREIGN KEY(product_id)
        REFERENCES product(id) ON UPDATE CASCADE ON DELETE RESTRICT;
