DROP TRIGGER startTrack
GO

CREATE TRIGGER startTrack ON [dbo].[PEIHUO] 
FOR INSERT
AS

INSERT INTO web_ordertrack(sysdno, orderNo, dno, trackType, trackTime, info, operator)
SELECT i.dno, w.orderno, i.dno, 2, getdate(), '配载，发往'+i.destination, '系统自动'
  FROM INSERTED i 
 INNER JOIN airwaybill a ON i.DNO = a.dno 
 INNER JOIN web_order w ON a.weborder = w.id
 WHERE ISNULL(a.weborder,0) <> 0 and i.stype = '配货'




