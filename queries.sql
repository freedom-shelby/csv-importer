-- Query 1: display all transactions for a batch (filter: merchant_id + batch_date + batch_ref_num)

SELECT t.id, t.batch_id, t.trans_date, t.trans_type, t.trans_card_type, t.trans_card_num, t.trans_amount
FROM transactions t
WHERE t.batch_id = 'bc9f1427-32b3-5bb8-b133-b6ae4a6a8677';

-- Query 2: display statistics for a batch (filter: merchant_id + batch_date + batch_ref_num)
--          grouped by transaction card type

SELECT b.merchant_id, b.batch_date, b.batch_ref_num, t.trans_card_type, COUNT(*)
FROM batches b
         JOIN transactions t ON b.id = t.batch_id
WHERE t.batch_id = '054fd532-dd8d-5fee-9d74-ca4f30f3883d'
GROUP BY t.trans_card_type;

-- Query 3: display top 10 merchants (by total amount) for a given date range (batch_date)
--          merchant id, merchant name, total amount, number of transactions

SELECT m.id, m.name, COALESCE(SUM(t.trans_amount), 0) AS amount, COUNT(*)
FROM merchants m
         LEFT JOIN batches b ON m.id = b.merchant_id
         LEFT JOIN transactions t ON b.id = t.batch_id
WHERE b.batch_date >= '1991-01-01'
  AND b.batch_date <= '2023-01-01'
GROUP BY b.merchant_id
ORDER BY amount DESC
LIMIT 10;

