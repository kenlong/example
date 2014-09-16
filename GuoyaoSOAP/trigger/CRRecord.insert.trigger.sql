DROP TRIGGER recordTrack
GO

CREATE TRIGGER recordTrack ON [dbo].[CRRecord] 
FOR INSERT
AS

INSERT INTO web_ordertrack(sysdno, orderNo, dno, trackType, trackTime, info, operator)
SELECT i.sysdno, w.orderno, i.sysdno, 0, getdate(), i.answer, i.operator
  FROM INSERTED i 
 INNER JOIN airwaybill a ON i.sysdno = a.dno 
 INNER JOIN web_order w ON a.weborder = w.id
 WHERE ISNULL(a.weborder,0) <> 0
